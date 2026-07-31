# Laravel Public Image Processing

Date: 2026-07-31

## Goal

Adopt Laravel 13's first-party image pipeline for public user uploads so the
marketplace, lost/found cases, sightings, and forum topics no longer retain
unbounded camera originals in mixed formats.

## Design

- `StorePublicImage` is the single application boundary around
  `Illuminate\Image\ImageManager`.
- Accepted uploads are content-validated before the Action runs, auto-oriented
  from EXIF data, proportionally scaled down to a 2560 by 2560 pixel box, and
  re-encoded as WebP at quality 82.
- Scaling never enlarges a smaller source image. Processing creates a random
  framework-generated filename and stores only the processed result on the
  public disk.
- Marketplace galleries, lost/found case galleries, sighting photos, and forum
  topic images use the same Action. Video storage is unchanged.
- Private care, medical, credential, and forum-journal files keep their
  existing authorization, private-disk, provenance, and retention semantics;
  this public-media change does not weaken those boundaries.

## Runtime Contract

`intervention/image:^4.0` is a production dependency required by Laravel's
image drivers. `IMAGE_DRIVER` selects `gd` or `imagick`; the selected PHP
extension must be installed. Public image limits and output settings live in
`config/images.php` and runtime code does not read environment values directly.

## Failure Contract

Malformed content, unsupported processing, and storage failures return a
localized validation error in English, Lithuanian, or Russian. Source file
names are never used as storage paths and processing exceptions are not shown
to the user.

## Verification Contract

`tests/Feature/PublicImageProcessingTest.php` proves WebP conversion, bounded
dimensions, no enlargement, generated storage paths, and safe malformed-image
failure. Marketplace, forum, lost/found case, and sighting feature tests prove
that every public upload seam is wired to the shared pipeline.
