# Changelog

All notable changes to `graper` will be documented in this file.

## [1.0.0] - 2026-04-26

### Added

- **GraperPage Model** - Eloquent model for storing GrapeJS page content with html, css, and project_data fields
- **GraperPageResource** - Filament admin resource for managing pages (list, create, edit)
- **GrapesJsField** - Filament form component with inline GrapeJS editor embedded in Alpine.js
- **GraperPlugin** - Filament plugin integration
- **BlockRegistry** - Singleton registry for registering custom blocks
- **BlockRegistry API Endpoint** - `GET /graper/api/blocks` returns all available blocks as JSON
- **Page Content API** - `GET/PUT /graper/api/page/{id}` for loading and saving page content
- **Public Page Display** - `GET /pages/{slug}` displays published pages
- **Edit Page Route** - `GET /graper/edit/{id}` opens the page in edit mode

### Included Blocks

- HeroBlock - Full-width hero section with CTA
- HeroVideoBlock - Hero section with video background
- CtaBlock - Call-to-action banner
- Cta50_50Block - Two-column CTA layout
- FeaturesGridBlock - Feature cards grid
- StatsBlock - Statistics/numbers display
- TestimonialsBlock - Customer testimonials carousel
- TeamMembersBlock - Team member profiles
- PricingTableBlock - Pricing comparison table
- FaqAccordionBlock - FAQ accordion
- NewsletterBlock - Newsletter signup form
- ImageGalleryBlock - Image gallery grid
- ContactSectionBlock - Contact information section
- FooterBlock - Footer with links and social
- SimpleTextBlock - Rich text content block

### Features

- Drag-and-drop block building with GrapeJS
- Live inline editing in Filament admin panel
- Page publish/unpublish status
- Custom block registration via BlockRegistry
- ComponentTrait system for sidebar controls
- Tailwind CSS styled blocks
- Eloquent storage driver for page content
- Configurable page route prefix
- Database migrations included

### Dependencies

- PHP 8.2+
- Laravel 11/12/13
- Filament 3.2+/5.0+
- Spatie Laravel Package Tools

### Documentation

- Comprehensive README with usage guide
- Installation instructions
- Custom block development guide