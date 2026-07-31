# Eloquent Review Summary Aggregation

Date: 2026-07-31

## Goal

Keep the denormalized review summary on `expert_profiles` correct while reducing
database round trips after a verified review is published.

## Design

- `ExpertProfile::publishedReviews()` is the single relationship boundary for
  reviews that may contribute to a public summary.
- `ExpertProfile::scopeWithPublishedReviewSummary()` selects only the profile
  key and adds aliased `withCount` and `withAvg` subqueries.
- `CreateReview` reads the three aggregate values from that one projected model
  result before updating the denormalized summary columns.
- Hidden and otherwise unpublished reviews remain excluded. The verified count
  remains independently constrained by `is_verified_client`.

## Query Delta

The aggregate refresh changes from three database round trips (`count`,
verified `count`, and `avg`) to one profile `select` containing two correlated
count subqueries and one correlated average subquery. The existing
`reviews_profile_status_verified_idx` composite index supports the relationship
constraints, so no schema change is required.

## Verification Contract

`tests/Feature/ExpertSafetyTest.php` proves that the refresh emits one aggregate
select, counts both published reviews, counts only the verified published
review, averages only published ratings, and excludes a hidden review.
