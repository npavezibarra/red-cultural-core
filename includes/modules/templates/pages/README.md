# Red Cultural - Trip Page Workflow

This document explains the repeatable process for building a new trip page from a PDF reference.

Use this workflow when the next trip arrives as a PDF and the slug is provided separately.

## Goal

Create a new custom trip page with:

- its own slug
- its own page template
- its own interest form
- its own email template
- its own admin/email tester entry
- its own gallery and hero assets
- the same overall structure we already use for trip pages

## Inputs Needed

- The PDF with the trip content
- The page slug from the user
- The final destination email recipients, if they differ from the default
- The image URLs to use for hero, gallery, and other visual sections

## Recommended Workflow

1. Extract the PDF content
   - Read the PDF carefully and identify:
     - trip title
     - dates
     - itinerary by day
     - rates
     - inclusions and exclusions
     - conditions and cancellation policy
     - contact details
     - any special copy for the hero or interest section

2. Duplicate the trip template structure
   - Start from the closest existing trip page template.
   - For this project, `viaje-italia.php` is usually the base reference.
   - Create a new file under:
     - `templates/pages/`
   - Name it with the new slug, for example:
     - `viaje-nuevo-slug.php`

3. Keep the layout, replace the content
   - Preserve the page structure unless the PDF requires a new block.
   - Replace only the written content first.
   - Then swap the images if the user provides new URLs.
   - If a gallery must show only one row, adjust the grid to match the number of images and the design intent.

4. Create a dedicated interest form
   - The form must use its own:
     - `action`
     - nonce
     - POST field names
     - success query param
     - redirect URL
   - Do not reuse the form identifiers from another trip page.
   - The success state should:
     - appear once after submit
     - hide only the form controls, not the whole section
     - clear the URL query string after rendering so refresh does not show the success state again

5. Create a dedicated email template
   - Add a file in `templates/emails/` for the trip.
   - The email should include:
     - trip name
     - trip dates
     - sender details from the form
     - the user's message
   - Keep the template self-contained.
   - Do not rely on the email body of another trip.

6. Wire the handler
   - Add the new `admin_post_nopriv_...` and `admin_post_...` hooks.
   - Create a dedicated handler method for the trip.
   - The handler should:
     - verify nonce
     - verify anti-spam fields
     - sanitize input
     - send the email using the dedicated template
     - redirect back to the trip page with a success or error flag

7. Wire the router
   - Add a new template redirect rule in:
     - `includes/modules/templates/routing/class-rc-templates-router.php`
   - Match the slug, fallback slug, and page ID if needed.
   - Load the new page template file from `templates/pages/`.

8. Add the form recipient configuration
   - Add the new trip ID to:
     - `includes/modules/templates/admin/class-rc-templates-admin.php`
   - Add the form ID to:
     - `includes/modules/templates/handlers/class-rc-templates-handlers.php`
   - Add the email tester entry in:
     - `includes/modules/email-tester/class-rc-email-tester.php`
   - If needed, teach the email log how to classify the new trip email.

9. Update any global content filters
   - If the new page is one of the special trip pages, add it to any content stripping or page detection logic.
   - Check:
     - `includes/modules/templates/class-rc-templates.php`
     - any email log classification helpers

10. Verify visually and functionally
   - Run `php -l` on every edited PHP file.
   - Load the page in the browser and confirm:
     - hero image
     - title
     - itinerary
     - gallery
     - interest form
     - success state
     - email content

## Naming Conventions

- Page slug:
  - `viaje-algo`
- Page template:
  - `templates/pages/viaje-algo.php`
- Email template:
  - `templates/emails/viaje-algo-interest.php`
- Form action:
  - `rcp_viaje_algo_interest`
- Nonce:
  - `rcp_viaje_algo_nonce`
- Success query param:
  - `rcp_algo_interest=success`

## Practical Notes

- Keep the same visual system unless the user asks for a redesign.
- Use the PDF as the source of truth for the written content.
- If a section is not in the PDF, do not invent copy.
- If a form or email still references another trip, treat that as a bug and separate it.
- If image URLs are supplied later, replace only the image source values, not the structure.

## Checklist Before Delivery

- New page file exists
- Slug resolves correctly
- Hero uses the correct image
- Gallery uses the intended images
- Interest form posts to the dedicated handler
- Success message disappears after refresh
- Email subject and body match the new trip
- Admin recipients entry exists
- Email tester entry exists
- `php -l` passes

