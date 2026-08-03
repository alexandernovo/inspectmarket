# E-Inspect wireframe coverage

This inventory maps all 285 supplied PowerPoint slides to implemented routes, views, components, dialogs, filters, or printable documents. Title-divider slides are identified explicitly; repeated slides represent a filter, modal, drawer, workflow step, or record-state variation rather than a separate database feature.

## Homepage — 14 slides

| Slides | Wireframe state | Implementation |
| --- | --- | --- |
| 1 | Deck title | Documentation divider |
| 2 | Public landing page | `/` |
| 3 | Five-user portal selector | `/roles/login` and `/roles/register` |
| 4 | Contact screen and form | `/public/contact` |
| 5 | Stall-rental service landing | `/public/stall-rental` |
| 6 | Stall-application form state | `/public/stall-application`; tenant application dialog |
| 7 | Complete stall-location map | `/public/stall-location` |
| 8 | Stall availability/occupancy summary | Public stall map legend and administrator stall-section editor |
| 9 | Slaughtered-inspection service landing | `/public/slaughtered-inspection` |
| 10 | Beef, poultry, and pork selector | Livestock selection cards |
| 11–13 | Poultry, pork, and beef request forms with calendar | `/public/inspection-request?type=...` |
| 14 | Public announcement totals and feed | `/public/announcements` |

## Tenant — 21 slides

| Slides | Wireframe state | Implementation |
| --- | --- | --- |
| 1 | Deck title | Documentation divider |
| 2–4 | Phone registration, verification code, password setup | `/register/tenant`, `/account/verify`, `/account/password` |
| 5–6 | Portal selector and tenant login | `/roles/login`, `/portal/tenant/login` |
| 7 | Tenant dashboard | `/tenant/dashboard` |
| 8 | Stall-location map | `/tenant/stall-map` |
| 9 | Application records | `/tenant/stall-applications` |
| 10 | Permit preview and application form | Application dialog, uploads, and application detail |
| 11 | Payment landing state | `/tenant/payments` |
| 12 | Tenant/stall/payment detail | Payment table and application detail |
| 13 | Official receipt | `/payments/{payment}/receipt` |
| 14 | Message drawer/conversation | Top-bar drawer and `/messages` |
| 15 | Notification drawer/feed | Top-bar drawer and `/notifications` |
| 16 | Inspection request records and statuses | `/tenant/inspection-requests` |
| 17–19 | Poultry, pork, and beef request/calendar states | Request dialog with livestock selector and detailed animal fields |
| 20 | Tenant profile | `/profile` |
| 21 | Logout confirmation | Shared logout dialog |

## Inspector — 32 slides

| Slides | Wireframe state | Implementation |
| --- | --- | --- |
| 1 | Deck title | Documentation divider |
| 2–4 | Phone registration, OTP, password | Inspector registration workflow |
| 5–6 | Portal selector and inspector login | Role selector and `/portal/inspector/login` |
| 7 | Inspector dashboard | `/inspector/dashboard` |
| 8–9 | Poultry list and inspection form | `/inspector/inspections?type=poultry` and review dialog |
| 10–11 | Pork list and inspection form | `/inspector/inspections?type=pork` and review dialog |
| 12–13 | Beef list and inspection form | `/inspector/inspections?type=beef` and review dialog |
| 14–16 | Pending, approved, and disapproved request filters | Inspection status badges/searchable table |
| 17–25 | Request detail plus poultry/pork/beef approval and disapproval dialogs | Inspection detail and review dialogs with status, schedule, reason, and remarks |
| 26 | Slaughtered-livestock report selector | `/inspector/reports` |
| 27–29 | Poultry, pork, and beef monthly reports | Report livestock filter, CSV, and print |
| 30 | Inspector profile | `/profile` |
| 31 | Message drawer | Top-bar drawer and `/messages` |
| 32 | Logout confirmation | Shared logout dialog |

## Clerk — 48 slides

| Slides | Wireframe state | Implementation |
| --- | --- | --- |
| 1 | Deck title | Documentation divider |
| 2–4 | Phone registration, OTP, password | Clerk registration workflow |
| 5–6 | Portal selector and clerk login | Role selector and `/portal/clerk/login` |
| 7–9 | Dashboard and stall-rental record preview | `/clerk/dashboard`, `/clerk/stall-rentals` |
| 10–11 | Cash-ticket five-step workflow and selected step | `/clerk/cash-ticket-collections` workflow component |
| 12–14 | Requisition/issue slip entry and completion | Assignment records and `/cash-ticket-assignments/{assignment}/slip` |
| 15–17 | Assign-collector step, dialog, and completed state | Treasurer assignment workflow shown to clerk |
| 18–22 | Collection step, calendar, collection dialog, and completed state | Collection calendar and add-collection dialog |
| 23–25 | Report step, report document, and completed state | `/cash-ticket-collections/{collection}/report` |
| 26–28 | Submit-to-treasurer step and printable final report | Collection status, report, and print view |
| 29 | Stall-rental landing | `/clerk/stall-rentals` |
| 30–33 | All, pending, paid, and unpaid tenant tables | Searchable rental/payment status records |
| 34 | Tenant stall/payment detail | Stall application detail |
| 35 | Official receipt preview | Payment receipt print view |
| 36–37 | Clerk report selector states | `/clerk/reports` |
| 38–43 | All and per-section stall-rental fee reports | Section filter, CSV, print |
| 44 | Cash-ticket report selector | Cash-ticket report option |
| 45 | Cash-ticket collected-fee report | Monthly cash-ticket report |
| 46 | Clerk profile | `/profile` |
| 47 | Message drawer | Top-bar drawer and `/messages` |
| 48 | Logout confirmation | Shared logout dialog |

## Treasurer — 84 slides

| Slides | Wireframe state | Implementation |
| --- | --- | --- |
| 1 | Deck title | Documentation divider |
| 2–4 | Phone registration, OTP, password | Treasurer registration workflow |
| 5–6 | Portal selector and treasurer login | Role selector and `/portal/treasurer/login` |
| 7–9 | Dashboard and tenant rental preview | `/treasurer/dashboard`, rental records |
| 10–19 | Announcement list, category tabs, and five category editors | `/treasurer/announcements`, filter tabs, editor dialog |
| 20 | Cash-ticket workflow landing | `/treasurer/cash-ticket-assignments` |
| 21–22 | Collector list and add-collector form | `/treasurer/collectors` |
| 23–28 | Workflow step states, calendars, and issue slip | Assignment workflow and printable slip |
| 29–31 | Collector assignment states and dialog | Assignment form/table |
| 32–38 | Collection calendar, fees, daily totals, report, and print document | Clerk collection records and print views |
| 39 | Stall-rental landing | `/treasurer/stall-rentals` |
| 40–44 | Rental status tables and tenant detail | Rental table filters and application detail |
| 45–50 | Pending/approved/disapproved application lists and decision dialogs | Review form, documents, stall assignment, approval/disapproval |
| 51 | Stall map | Stall map component |
| 52–57 | Tenant lists, status variations, actions, and detail | Rental records and tenant detail |
| 58–59 | Editable stall map and section totals dialog | Administrator/treasurer map states and section counts |
| 60–65 | Report role/type selectors | `/treasurer/reports` |
| 66–71 | All/per-section tenant reports | Section-filtered stall-rental reports |
| 72–73 | Clerk report selector variants | Clerk cash-ticket/stall report options |
| 74–78 | Stall-fee section reports | Section filters |
| 79–80 | Cash-ticket selector and fee report | Cash-ticket monthly report |
| 81 | Settings state | Account/system settings screens |
| 82 | Treasurer profile | `/profile` |
| 83 | Message drawer | Top-bar drawer and `/messages` |
| 84 | Logout confirmation | Shared logout dialog |

## Administrator — 86 slides

| Slides | Wireframe state | Implementation |
| --- | --- | --- |
| 1 | Deck title | Documentation divider |
| 2–3 | Role selector and administrator login | Role selector and `/portal/administrator/login` |
| 4 | Administrator dashboard and charts | `/administrator/dashboard` |
| 5 | Treasurer record selector | `/administrator/records/treasurer` |
| 6–17 | Treasurer rental/application states, details, map, section editor, and tenant tables | Treasurer drill-down records and `/administrator/stall-map` |
| 18–19 | Clerk record selector variants | `/administrator/records/clerk` |
| 20–35 | Clerk cash-ticket workflow, calendars, slips, assignments, reports, and printable report | Clerk drill-down, assignment slip, collection report |
| 36–40 | Clerk stall selector and rental status tables | Clerk stall-rental drill-down |
| 41 | Inspector record selector | `/administrator/records/inspector` |
| 42–47 | Poultry, pork, and beef lists/forms | Inspector drill-down and inspection detail |
| 48 | Tenant record selector | `/administrator/records/tenant` |
| 49–54 | Tenant rental/payment states and tenant detail | Tenant drill-down, application and payment details |
| 55–60 | Report role/type selectors and treasury documents | `/administrator/reports` and print views |
| 61–66 | Per-section tenant reports | Section-filtered reports |
| 67–71 | Inspector report selector and livestock reports | Inspection report filters |
| 72–74 | Clerk report selectors | Clerk report drill-down |
| 75–80 | Stall-rental section reports | Section-filtered reports |
| 81–82 | Cash-ticket selector and collected-fee report | Cash-ticket report |
| 83 | Settings | `/administrator/settings` |
| 84 | Administrator profile | `/profile` |
| 85 | Message drawer | Top-bar drawer and `/messages` |
| 86 | Logout confirmation | Shared logout dialog |

## Validation target

The six deck counts are 14 + 21 + 32 + 48 + 84 + 86 = **285 slides**. Automated tests cover route rendering, authorization, registration, password recovery, public requests, uploads, tenant workflows, inspection decisions, collector assignments, collections, payments, reports, record drill-downs, messages, notifications, and exports.
