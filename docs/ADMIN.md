# Admin usage

Both applications sign in at `/admin`. Create the first administrator with `php artisan karako:admin you@example.com` inside `holding/` or `tourism/`. The admin chrome is Persian. Missing admin strings fall back to English. Managed content is stored per language: Persian, English, Russian, and Arabic.

Two roles exist. An **administrator** can manage users as well as content. An **editor** can create and edit content, and can review inquiries, but cannot open user management. Policies enforce that boundary. Hiding the Users link is only the visible part of it.

## Shared habits

- The sidebar groups the sections. On a narrow screen, open it with Menu.
- Lists accept a search, a published or draft filter where the record has one, and a sort. Results are paged.
- Leave **Published** unchecked to keep a draft off the public site. Publishing needs a title in at least one language.
- Validation errors appear at the top of the form and on the fields. A successful save shows “Saved.”
- Deleting a record requires the confirmation checkbox. The server rejects the request without it.
- Images are limited to JPEG, PNG, and WebP and are re-encoded before they are stored. A stored cover is previewed on the edit form.
- Order is a number. The holding division list saves order and publication only for the rows on the current page.
- Related saves, such as a record plus its translations, run in one database transaction.

## Holding

| Task | Where |
| --- | --- |
| Homepage text, hero, and video | Site → Homepage |
| Company profile and sharing image | Site → Company profile |
| Menu labels and links | Site → Navigation |
| Phone, address, hours, and social links | Site → Contact details |
| Division text, image, link, order, and publication | Content → Divisions |
| Projects | Content → Projects |
| Custom pages and SEO fields | Content → Pages |
| Contact messages | Messages → Inquiries |

Unpublish a division, project, or page to remove it from the public site. The record stays in the admin. A public URL is shown only when that language has its own title and slug. A missing translation is a not-found page, not a copy of another language.

## Tourism

| Task | Where |
| --- | --- |
| Homepage sections and contact details | Site → Homepage |
| Informational pages and questions | Site → Pages and Questions |
| Tours, including price, discount, departures, stays, and itinerary days | Catalog → Tours |
| Destinations, categories, styles, transport, and included services | Catalog |
| Accommodation | Catalog → Accommodation |
| Medical page, categories, services, clinics, doctors, journey steps, and questions | Medical tourism |
| Attractions and their categories | Places |
| Consultation requests | Messages → Consultation requests |
| Medical consultation requests | Messages → Medical requests |
| Reviews, complaints, and surveys | Messages → Feedback and Surveys |

Save a tour once for its text, price, discount, dates, hotels, and day-by-day plan. Those rows are stored together. A discount is either a lower price in the same currency or a percent.

A medical or attraction record stays a draft until it is published and has a title in at least one language. Doctor profiles have no fields for prices, outcomes, or certificates. The public medical pages keep the notice that the section is not medical advice.

The public phrase “UNESCO World Heritage Site” appears only when the designation is World Heritage and **Verified UNESCO World Heritage Site** is checked. Check that box only after the designation has been confirmed. The inscription date and reference are shown only in that case. Cultural, natural, or mixed classification is a separate label.

Optional covers, galleries, videos, summaries, and related tours are left out of the public page when they are empty.

## Languages on the public site

Each public URL is `/{locale}/...` for `fa`, `en`, `ru`, and `ar`. Persian and Arabic use a right-to-left layout. English and Russian use left-to-right. Navigation labels and system messages come from the language files.

Editorial text comes from the translation stored for that record:

- A detail URL requires a real translation in that language. If it is missing, the page is not found.
- A list may show a title from another language only when the link goes to the locale that owns that translation.
- `hreflang` for a missing translation points at that language’s homepage, not at a page written in another language.
- Section landings exist in every language. Their editorial body is omitted when that language’s row is empty.
- SEO title, description, and canonical URL are stored on the translation. The canonical value, when set, must be an `http` or `https` URL.

## Not implemented

These workflows are intentionally absent:

- Inquiry and feedback email. Replies are stored for the team.
- Booking confirmation, payment, and seat inventory.
- A Russian or Arabic admin chrome. Editors enter those languages as content; the admin screens stay Persian, with English as the string fallback.
- Drag-and-drop ordering, and an audit log of who edited a record.
- Map tiles, unless `MAP_TILE_URL` is set on the server.
- Medical records, diagnoses, or file uploads on the consultation form.
- Invented prices, clinic credentials, treatment outcomes, or UNESCO facts. Enter only what the team can stand behind.
