@php
  $currentUserId = auth()->id();
  $sessionBackgroundPath = session('filament_random_bg_path');
  $sessionBackgroundUserId = session('filament_random_bg_user_id');

  if (! is_string($sessionBackgroundPath) || $sessionBackgroundPath === '' || $sessionBackgroundUserId !== $currentUserId) {
    $backgroundFiles = glob(public_path('assets/bg/*.{jpg,jpeg,png,webp,avif,gif}'), GLOB_BRACE) ?: [];

    $selectedBackgroundPath = $backgroundFiles !== []
      ? $backgroundFiles[array_rand($backgroundFiles)]
      : public_path('assets/pattaya.webp');

    $sessionBackgroundPath = str_replace(public_path(), '', $selectedBackgroundPath);
    $sessionBackgroundPath = str_replace('\\', '/', $sessionBackgroundPath);

    session([
      'filament_random_bg_path' => $sessionBackgroundPath,
      'filament_random_bg_user_id' => $currentUserId,
    ]);
  }

  $appUrl = rtrim((string) config('app.url'), '/');
  $backgroundImageUrl = $appUrl . '/' . ltrim($sessionBackgroundPath, '/');
@endphp

<style>
  .filament-random-bg {
    position: fixed;
    inset: 0;
    z-index: 1;
    pointer-events: none;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0.6;
      background-size: cover;
  /* Add a semi-transparent background color for better visual depth (optional) */
  background-color: rgba(255, 255, 255, 0.4);
  /* Apply the blur effect to the background area */
  backdrop-filter: blur(8px);
  /* Add -webkit- prefix for wider compatibility, especially older Safari/iOS */
  -webkit-backdrop-filter: blur(8px);
  /* Other styling for the container */

  }
  .fi-main.fi-width-full{
    z-index: 10;
  }
</style>
<div class="filament-random-bg" aria-hidden="true" style="background-image: url('{{ $backgroundImageUrl }}');"></div>
