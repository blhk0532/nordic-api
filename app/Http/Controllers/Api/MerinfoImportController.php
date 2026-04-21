<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merinfo;
use App\Models\Person;
use App\Models\SwedenPersoner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MerinfoImportController extends Controller
{
    public function import(Request $request): JsonResponse
    {
        $content = $request->getContent();
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        // Handle different input formats:
        // 1. { "results": [{ "items": [...] }] } - merinfo.se format
        // 2. [ ... ] - simple array
        // 3. { ... } - single object
        if (isset($decoded['results'][0]['items'])) {
            $items = $decoded['results'][0]['items'];
        } elseif (isset($decoded['results'])) {
            $items = $decoded['results'];
        } elseif (array_is_list($decoded)) {
            $items = $decoded;
        } else {
            $items = [$decoded];
        }

        Log::channel('merinfo')->info('merinfo.import.request', [
            'content_length' => strlen($content),
            'item_count' => count($items),
            'sample' => array_slice($items, 0, 3),
        ]);

        $success = 0;
        $failed = 0;
        $errors = [];
        $created = [];
        $updated = [];

        foreach ($items as $item) {
            try {
                $result = $this->createOrUpdateMerinfo($item);
                $success++;
                if ($result['created_at'] === $result['updated_at']) {
                    $created[] = $result;
                } else {
                    $updated[] = $result;
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = $e->getMessage();
                Log::channel('merinfo')->warning('merinfo.import.error', [
                    'error' => $e->getMessage(),
                    'data' => $item,
                ]);
            }
        }

        Log::channel('merinfo')->info('merinfo.import.response', [
            'success' => $success,
            'failed' => $failed,
        ]);

        return response()->json([
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors,
            'created' => $created,
            'updated' => $updated,
        ]);
    }

    private function createOrUpdateMerinfo(array $data): array
    {
        $shortUuid = $data['short_uuid'] ?? null;

        if (! $shortUuid) {
            throw new \Exception('short_uuid is required');
        }

        $address = $data['address'][0] ?? [];
        $addressString = is_array($address) ? ($address['street'] ?? '') : '';

        $isHouse = ! preg_match('/lgh|1 tr|2 tr|3 tr|4 tr|5 tr|6 tr| nb| bv|\bBox\b|\b([1-9][0-9]?|100)\s*[A-Z]\b/i', $addressString);

        $normalized = [
            'type' => $data['type'] ?? 'person',
            'short_uuid' => $shortUuid,
            'name' => $data['name'] ?? null,
            'givenNameOrFirstName' => $data['givenNameOrFirstName'] ?? null,
            'personalNumber' => $data['personalNumber'] ?? null,
            'pnr' => isset($data['pnr']) && is_array($data['pnr']) ? $data['pnr'] : [],
            'address' => isset($data['address'][0]) && is_array($data['address'][0]) ? $data['address'][0] : [],
            'gender' => $data['gender'] ?? null,
            'is_celebrity' => $data['is_celebrity'] ?? false,
            'has_company_engagement' => $data['has_company_engagement'] ?? false,
            'is_house' => $isHouse,
            'number_plus_count' => $data['number_plus_count'] ?? 0,
            'phone_number' => isset($data['phone_number'][0]) && is_array($data['phone_number'][0]) ? $data['phone_number'][0] : [],
            'url' => $data['url'] ?? null,
            'same_address_url' => $data['same_address_url'] ?? null,
        ];

        $existing = Merinfo::where('short_uuid', $normalized['short_uuid'])->first();

        if ($existing) {
            $existing->update(array_filter($normalized, fn ($v) => $v !== null));
            $this->syncToPersons($existing);
            $this->syncToSwedenPersoner($existing);

            return [
                'id' => $existing->id,
                'short_uuid' => $existing->short_uuid,
                'created_at' => $existing->created_at?->toIso8601String(),
                'updated_at' => $existing->updated_at?->toIso8601String(),
            ];
        }

        $created = Merinfo::create($normalized);
        $this->syncToPersons($created);
        $this->syncToSwedenPersoner($created);

        return [
            'id' => $created->id,
            'short_uuid' => $created->short_uuid,
            'created_at' => $created->created_at?->toIso8601String(),
            'updated_at' => $created->updated_at?->toIso8601String(),
        ];
    }

    private function syncToPersons(Merinfo $merinfo): void
    {
        // Address is stored as flat object in merinfo
        $address = is_array($merinfo->address) ? $merinfo->address : [];
        $street = $address['street'] ?? '';
        $zipCode = $address['zip_code'] ?? '';
        $city = $address['city'] ?? '';

        // Phone number - handle both array [{number: ...}] and flat object {number: ...}
        $phoneData = is_array($merinfo->phone_number) ? $merinfo->phone_number : [];
        $firstPhone = is_array($phoneData) ? ($phoneData[0] ?? $phoneData) : $phoneData;
        $phoneNumber = is_array($firstPhone) ? ($firstPhone['number'] ?? null) : (is_string($firstPhone) ? $firstPhone : null);

        $personData = [
            'name' => $merinfo->name,
            'street' => $street,
            'zip' => $zipCode,
            'city' => $city,
            'phone' => $phoneNumber,
            'merinfo_id' => $merinfo->id,
            'merinfo_phone' => $phoneNumber,
            'personal_number' => $merinfo->personalNumber,
            'gender' => $merinfo->gender,
            'merinfo_is_house' => $merinfo->is_house,
        ];

        $existingPerson = Person::where('merinfo_id', $merinfo->id)->first();

        if ($existingPerson) {
            $existingPerson->update(array_filter($personData, fn ($v) => $v !== null));

            return;
        }

        Person::create(array_filter($personData, fn ($v) => $v !== null));
    }

    private function syncToSwedenPersoner(Merinfo $merinfo): void
    {
        $address = is_array($merinfo->address) ? $merinfo->address : [];
        $street = $address['street'] ?? '';
        $zipCode = $address['zip_code'] ?? '';
        $city = $address['city'] ?? '';

        $phoneData = is_array($merinfo->phone_number) ? $merinfo->phone_number : [];
        $phoneArray = is_array($phoneData) ? (array_is_list($phoneData) ? $phoneData : [$phoneData]) : [];
        $firstPhoneItem = $phoneArray[0] ?? null;
        $phoneNumber = is_array($firstPhoneItem) ? ($firstPhoneItem['number'] ?? null) : (is_string($firstPhoneItem) ? $firstPhoneItem : null);

        // Map names
        $names = explode(' ', (string) $merinfo->name);
        $firstName = $merinfo->givenNameOrFirstName ?? ($names[0] ?? null);
        $lastName = count($names) > 1 ? end($names) : null;

        $swedenPersonData = [
            'personnamn' => $merinfo->name,
            'fornamn' => $firstName,
            'efternamn' => $lastName,
            'adress' => $street,
            'postnummer' => $zipCode,
            'postort' => $city,
            'personnummer' => $merinfo->personalNumber,
            'kon' => $merinfo->gender,
            'telefon' => $phoneNumber,
            'telefonnummer' => $phoneArray,
            'is_hus' => $merinfo->is_house,
            'merinfo_link' => $merinfo->url,
            'merinfo_data' => $merinfo->toArray(),
        ];

        // Use updateOrCreate with the unique constraint keys (adress, fornamn, efternamn)
        // This handles duplicate key violations by updating instead of failing
        SwedenPersoner::updateOrCreate(
            [
                'adress' => $street,
                'fornamn' => $firstName,
                'efternamn' => $lastName,
            ],
            array_filter($swedenPersonData, fn ($v) => $v !== null)
        );
    }
}
