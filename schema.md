# Schema

This document summarizes the current state machines for core components, routes, and controllers.
It is derived from `routes/*.php` and the controller list under `app/Http/Controllers`.

## Global access flow

```mermaid
stateDiagram-v2
  [*] --> Guest
  Guest --> RegisterForm: GET /register (RegisteredUserController@create)
  Guest --> Registering: POST /register (RegisteredUserController@store)
  Guest --> LoginForm: GET /login (AuthenticatedSessionController@create)
  LoginForm --> Authenticated: POST /login (AuthenticatedSessionController@store)
  Guest --> PasswordResetRequest: GET /forgot-password (PasswordResetLinkController@create)
  PasswordResetRequest --> PasswordResetEmailSent: POST /forgot-password (PasswordResetLinkController@store)
  Guest --> PasswordResetForm: GET /reset-password/{token} (NewPasswordController@create)
  PasswordResetForm --> Authenticated: POST /reset-password (NewPasswordController@store)

  Authenticated --> VerifyEmailPrompt: GET /verify-email (EmailVerificationPromptController)
  VerifyEmailPrompt --> Authenticated: GET /verify-email/{id}/{hash} (VerifyEmailController)

  Authenticated --> RoleSelect: GET /role-select (UserController@roleSelector)
  Authenticated --> InstitutionSelect: GET /institution-select (UserController@institutionSelector)
  RoleSelect --> RoleSelected: POST /profile/role (UserController@setUserRoleForSession)
  InstitutionSelect --> InstitutionSelected: POST /profile/institution (UserController@setUserInstitutionForSession)

  InstitutionSelected --> Dashboard: GET /dashboard (UserController@dashboard)
  Dashboard --> ProfileEdit: GET /profile (ProfileController@edit)
  ProfileEdit --> Dashboard: PATCH /profile (ProfileController@update)
  ProfileEdit --> DeletedUser: DELETE /profile (ProfileController@destroy)

  Authenticated --> LoggedOut: POST /logout (AuthenticatedSessionController@destroy)
```

## Users component

```mermaid
stateDiagram-v2
  [*] --> UsersIndex
  UsersIndex: GET /users (PaginatedUserController@index)

  UsersIndex --> UsersFilter: GET /users/filter (UserController@filter)
  UsersFilter --> UsersFilterResult: GET /users/filter/result (UserController@filterResult)
  UsersIndex --> UsersSearch: GET /users/search (UserController@search)

  UsersIndex --> UserCreateForm: GET /users/create (UserController@create)
  UserCreateForm --> UserStored: POST /users (UserController@store)

  UsersIndex --> UserEditForm: GET /users/{user} (UserController@edit)
  UserEditForm --> UserUpdated: POST /users/{user} (UserController@update)
  UserEditForm --> UserDisabled: DELETE /users/{user} (UserController@destroy)

  UserEditForm --> UserRolesUpdated: POST /user-roles/{user} (UserController@updateRoles)
  UserEditForm --> UserPictureUpdated: PUT /users/{user}/picture (UserController@picture)
  UserEditForm --> UserPfpUpdated: PUT /profile/{user}/picture (UserController@userUploadPicture)
  UserEditForm --> UserLanguagesUpdated: POST /users/{user}/languages (UserController@languages)

  UserEditForm --> WeaponFormsTech: POST /users/{user}/weapon-forms-technician (UserController@editWeaponFormsTechnician)
  UserEditForm --> WeaponFormsPersonnel: POST /users/{user}/weapon-forms-personnel (UserController@editWeaponFormsPersonnel)
  UserEditForm --> WeaponFormsAthlete: POST /users/{user}/weapon-forms-athlete (UserController@editWeaponFormsAthlete)
  UserEditForm --> WeaponFormsDate: POST /users/{user}/weapon-forms-edit-date (UserController@editWeaponFormsAwardingDate)

  UsersIndex --> AssociateSchool: POST /users/associate-school (UserController@associateSchool)
  UsersIndex --> RemoveSchool: POST /users/remove-school (UserController@removeSchool)
  UsersIndex --> AssociateAcademy: POST /users/associate-academy (UserController@associateAcademy)
  UsersIndex --> RemoveAcademy: POST /users/remove-academy (UserController@removeAcademy)
  UsersIndex --> SetMainInstitution: POST /users/set-main-institution (UserController@setMainInstitution)

  UserEditForm --> PasswordReset: POST /user/{user}/reset-password (UserController@resetPassword)
```

## Institutions: nations, academies, schools, clans

```mermaid
stateDiagram-v2
  [*] --> NationsIndex
  NationsIndex: GET /nations (NationController@index)
  NationsIndex --> NationEdit: GET /nations/{nation} (NationController@edit)
  NationEdit --> NationUpdated: POST /nations/{nation} (NationController@update)
  NationEdit --> NationFlagUpdated: PUT /nations/{nation}/flag (NationController@updateFlag)
  NationsIndex --> NationAcademyAssociated: POST /nations/{nation}/academies (NationController@associateAcademy)
  NationsIndex --> NationAcademyCreated: POST /nations/{nation}/academies/create (AcademyController@storenation)

  [*] --> AcademiesIndex
  AcademiesIndex: GET /academies (AcademyController@index)
  AcademiesIndex --> AcademyCreateForm: GET /academies/create (AcademyController@create)
  AcademyCreateForm --> AcademyStored: POST /academies (AcademyController@store)
  AcademiesIndex --> AcademyEdit: GET /academies/{academy} (AcademyController@edit)
  AcademyEdit --> AcademyUpdated: POST /academies/{academy} (AcademyController@update)
  AcademyEdit --> AcademyDisabled: DELETE /academies/{academy} (AcademyController@destroy)
  AcademyEdit --> AcademyPictureUpdated: PUT /academies/{academy}/picture (AcademyController@picture)
  AcademyEdit --> AcademyPersonnelAdded: POST /academies/{academy}/add-personnel (AcademyController@addPersonnel)
  AcademyEdit --> AcademyPersonnelRemoved: POST /academies/{academy}/remove-personnel (AcademyController@removePersonnel)
  AcademyEdit --> AcademyAthleteAdded: POST /academies/{academy}/add-athlete (AcademyController@addAthlete)
  AcademyEdit --> AcademyAthleteRemoved: POST /academies/{academy}/remove-athlete (AcademyController@removeAthlete)
  AcademyEdit --> AcademySchoolAdded: POST /academies/{academy}/schools (AcademyController@addSchool)
  AcademyEdit --> AcademyUsersCreated: POST /academies/{academy}/users/create (UserController@storeForAcademy)

  [*] --> SchoolsIndex
  SchoolsIndex: GET /schools (SchoolController@index)
  SchoolsIndex --> SchoolCreateForm: GET /schools/create (SchoolController@create)
  SchoolCreateForm --> SchoolStored: POST /schools (SchoolController@store)
  SchoolsIndex --> SchoolEdit: GET /schools/{school} (SchoolController@edit)
  SchoolEdit --> SchoolUpdated: POST /schools/{school} (SchoolController@update)
  SchoolEdit --> SchoolDisabled: DELETE /schools/{school} (SchoolController@destroy)
  SchoolEdit --> SchoolClanAdded: POST /schools/{school}/clans (SchoolController@addClan)
  SchoolEdit --> SchoolPersonnelAdded: POST /schools/{school}/add-personnel (SchoolController@addPersonnel)
  SchoolEdit --> SchoolPersonnelRemoved: POST /schools/{school}/remove-personnel (SchoolController@removePersonnel)
  SchoolEdit --> SchoolAthleteAdded: POST /schools/{school}/athlete (SchoolController@addAthlete)
  SchoolEdit --> SchoolAthleteRemoved: POST /schools/{school}/remove-athlete (SchoolController@removeAthlete)
  SchoolEdit --> SchoolUsersCreated: POST /schools/{school}/users/create (UserController@storeForSchool)
  SchoolEdit --> SchoolClanCreated: POST /schools/{school}/clan/create (ClanController@storeForSchool)

  [*] --> ClansIndex
  ClansIndex: GET /courses (ClanController@index)
  ClansIndex --> ClanCreateForm: GET /courses/create (ClanController@create)
  ClanCreateForm --> ClanStored: POST /courses (ClanController@store)
  ClansIndex --> ClanEdit: GET /courses/{clan} (ClanController@edit)
  ClanEdit --> ClanUpdated: POST /courses/{clan} (ClanController@update)
  ClanEdit --> ClanDisabled: DELETE /courses/{clan} (ClanController@destroy)
  ClanEdit --> ClanUserCreated: POST /courses/{clan}/user/create (UserController@storeForClan)
  ClanEdit --> ClanInstructorAdded: POST /courses/{clan}/add-instructors (ClanController@addInstructor)
  ClanEdit --> ClanInstructorRemoved: POST /courses/{clan}/remove-instructors (ClanController@removeInstructor)
  ClanEdit --> ClanAthleteAdded: POST /courses/{clan}/add-athlete (ClanController@addAthlete)
  ClanEdit --> ClanAthleteRemoved: POST /courses/{clan}/remove-athlete (ClanController@removeAthlete)
```

## Events and event types

```mermaid
stateDiagram-v2
  [*] --> EventsIndex
  EventsIndex: GET /events (EventController@index)
  EventsIndex --> EventsCalendar: GET /events/calendar (EventController@calendar)
  EventsIndex --> EventCreateForm: GET /events/create (EventController@create)
  EventCreateForm --> EventStored: POST /events (EventController@store)

  EventsIndex --> EventEdit: GET /events/{event} (EventController@edit)
  EventEdit --> EventUpdated: POST /events/{event} (EventController@update)
  EventEdit --> EventDisabled: DELETE /events/{event} (EventController@destroy)
  EventEdit --> EventReview: GET /events/{event}/review (EventController@review)

  EventEdit --> EventReject: POST /events/{event}/reject (EventController@reject)
  EventEdit --> EventApprove: POST /events/{event}/approve (EventController@approve)
  EventEdit --> EventPublish: POST /events/{event}/publish (EventController@publish)

  EventEdit --> EventDescriptionSaved: POST /events/{event}/description (EventController@saveDescription)
  EventEdit --> EventLocationSaved: POST /events/{event}/location (EventController@saveLocation)
  EventEdit --> EventThumbnailUpdated: PUT /events/{event}/thumbnail (EventController@updateThumbnail)

  EventEdit --> EventParticipants: GET /events/{event}/participants (EventController@participants)
  EventParticipants --> EventParticipantAdded: POST /events/{event}/participants (EventController@addParticipant)
  EventParticipants --> EventParticipantsSelected: POST /add-participants (EventController@selectParticipants)
  EventParticipants --> EventParticipantsExport: GET /events/{event}/participants/export (EventController@exportParticipants)

  EventEdit --> EventPersonnel: GET /events/{event}/personnel (EventController@personnel)
  EventPersonnel --> EventPersonnelAdded: POST /events/{event}/add-personnel (EventController@addPersonnel)
  EventEdit --> EventAvailableUsers: GET /events/{event}/available-users (EventController@available)
  EventEdit --> EventAvailablePersonnel: GET /events/{event}/available-personnel (EventController@availablePersonnel)

  [*] --> EventTypesIndex
  EventTypesIndex: GET /event-types (EventTypeController@index)
  EventTypesIndex --> EventTypeCreate: POST /event-types/create (EventTypeController@store)
  EventTypesIndex --> EventTypeEdit: GET /event-types/{eventType} (EventTypeController@edit)
  EventTypeEdit --> EventTypeUpdated: POST /event-types/{eventType} (EventTypeController@update)
  EventTypeEdit --> EventTypeDisabled: DELETE /event-types/{eventType} (EventTypeController@destroy)
  EventTypeEdit --> EventTypeAssociated: POST /event-types/{eventType}/associate (EventTypeController@associate_event)
```

## Fees, orders, and shop flows

```mermaid
stateDiagram-v2
  [*] --> FeesIndex
  FeesIndex: GET /rector/fees (FeeController@index)
  FeesIndex --> FeesPurchase: GET /rector/fees/purchase (FeeController@create)
  FeesIndex --> FeesRenew: GET /rector/fees/renew (FeeController@renew)

  FeesIndex --> FeesStripeCheckout: GET /rector/fees/stripe/checkout (FeeController@checkoutStripe)
  FeesStripeCheckout --> FeesSuccess: GET /rector/fees/success (FeeController@success)
  FeesStripeCheckout --> FeesCancel: GET /rector/fees/cancel (FeeController@cancel)

  FeesIndex --> FeesPaypalCheckout: POST /rector/fees/paypal/checkout (FeeController@checkoutPaypal)
  FeesPaypalCheckout --> FeesPaypalSuccess: GET /rector/fees/paypal/success (FeeController@successPaypal)
  FeesPaypalCheckout --> FeesPaypalCancel: GET /rector/fees/paypal/cancel (FeeController@cancelPaypal)

  [*] --> OrdersIndex
  OrdersIndex: GET /orders (OrderController@index)
  OrdersIndex --> OrderEdit: GET /orders/{order} (OrderController@edit)
  OrderEdit --> OrderInvoiceUpdated: POST /orders-invoice/{order} (OrderController@invoice)
  OrderEdit --> OrderWireApproved: POST /orders/{order}/wire (OrderController@approveWireTransfer)

  [*] --> Shop
  Shop: GET /shop (ShopController@shop)
  Shop --> MembershipActivate: GET /shop/activate-membership (ShopController@activate)
  Shop --> ShopFeesStripeCheckout: GET /shop/fees/stripe/checkout (FeeController@userCheckoutStripe)
  Shop --> ShopFeesPaypalCheckout: POST /shop/fees/paypal/checkout (FeeController@userCheckoutPaypal)
  Shop --> ShopFeesWire: GET /shop/fees/wire-transfer (FeeController@userCheckoutWireTransfer)

  Shop --> EventPurchase: GET /event-purchase/{event} (EventController@purchase)
  EventPurchase --> EventStripeCheckout: GET /shop/event/{event}/stripe/checkout (EventController@userCheckoutStripe)
  EventPurchase --> EventStripePreauth: GET /shop/event/{event}/stripe/preauth (EventController@userPreauthorizeStripe)
  EventPurchase --> EventPaypalCheckout: POST /shop/event/{event}/paypal/checkout (EventController@userCheckoutPaypal)
  EventPurchase --> EventPaypalPreauth: POST /shop/event/{event}/paypal/preauth (EventController@userPreauthorizePaypal)
  EventPurchase --> EventWaitingListCheckout: POST /shop/event/{event}/waiting-list/checkout (EventController@userCheckoutWaitingList)
  EventPurchase --> EventFreeCheckout: POST /shop/event/{event}/free/checkout (EventController@userCheckoutFree)
```

## Imports and exports

```mermaid
stateDiagram-v2
  [*] --> ImportsIndex
  ImportsIndex: GET /imports (ImportController@index)
  ImportsIndex --> ImportCreateForm: GET /imports/create (ImportController@create)
  ImportCreateForm --> ImportStored: POST /imports (ImportController@store)
  ImportsIndex --> ImportEdit: POST /imports/{import} (ImportController@update)
  ImportsIndex --> ImportDownload: GET /imports/{import}/download (ImportController@download)
  ImportsIndex --> ImportTemplate: GET /imports/template (ImportController@template)
  ImportsIndex --> ImportDisabled: DELETE /imports/{import} (ImportController@destroy)

  [*] --> ExportsIndex
  ExportsIndex: GET /exports (ExportController@index)
  ExportsIndex --> ExportCreateForm: GET /exports/create (ExportController@create)
  ExportCreateForm --> ExportStored: POST /exports (ExportController@store)
  ExportsIndex --> ExportEdit: POST /exports/{export} (ExportController@update)
  ExportsIndex --> ExportDownload: GET /exports/{export}/download (ExportController@download)
  ExportsIndex --> ExportDisabled: DELETE /exports/{export} (ExportController@destroy)
```

## Announcements and roles

```mermaid
stateDiagram-v2
  [*] --> AnnouncementsIndex
  AnnouncementsIndex: GET /announcements (AnnouncementController@index)
  AnnouncementsIndex --> AnnouncementCreateForm: GET /announcements/create (AnnouncementController@create)
  AnnouncementCreateForm --> AnnouncementStored: POST /announcements (AnnouncementController@store)
  AnnouncementsIndex --> AnnouncementEdit: GET /announcements/{announcement} (AnnouncementController@edit)
  AnnouncementEdit --> AnnouncementUpdated: POST /announcements/{announcement} (AnnouncementController@update)
  AnnouncementEdit --> AnnouncementDisabled: DELETE /announcements/{announcement} (AnnouncementController@destroy)

  [*] --> RolesIndex
  RolesIndex: GET /custom-roles (RoleController@index)
  RolesIndex --> RolesSearch: GET /custom-roles/search (RoleController@search)
  RolesIndex --> RolesAssign: POST /custom-roles/assign (RoleController@assign)
  RolesIndex --> RolesStored: POST /custom-roles (RoleController@store)
```

## Weapon forms

```mermaid
stateDiagram-v2
  [*] --> WeaponFormsIndex
  WeaponFormsIndex: GET /weapon-forms (WeaponFormController@index)
  WeaponFormsIndex --> WeaponFormCreate: GET /weapon-forms/create (WeaponFormController@create)
  WeaponFormCreate --> WeaponFormStored: POST /weapon-forms (WeaponFormController@store)
  WeaponFormsIndex --> WeaponFormEdit: GET /weapon-forms/{weaponForm} (WeaponFormController@edit)
  WeaponFormEdit --> WeaponFormUpdated: POST /weapon-forms/{weaponForm} (WeaponFormController@update)
  WeaponFormEdit --> WeaponFormTechniciansAdded: POST /weapon-forms/{weaponForm}/technicians (WeaponFormController@addTechnicians)
  WeaponFormEdit --> WeaponFormPersonnelAdded: POST /weapon-forms/{weaponForm}/personnel (WeaponFormController@addPersonnel)
  WeaponFormEdit --> WeaponFormAthletesAdded: POST /weapon-forms/{weaponForm}/athletes (WeaponFormController@addAthletes)
  WeaponFormEdit --> WeaponFormImageUpdated: PUT /weapon-forms/{weaponForm}/image (AcademyController@image)
```

## Public website routing

```mermaid
stateDiagram-v2
  [*] --> PublicHomepage
  PublicHomepage: GET / (homepage view)
  PublicHomepage --> WebsiteEventsList: GET /events-list (EventController@eventsList)
  WebsiteEventsList --> WebsiteEventDetail: GET /events-detail/{event:slug} (EventController@show)

  PublicHomepage --> WebsiteRankings: GET /website-rankings (EventController@rankings)
  WebsiteRankings --> WebsiteRankingsGeneral: GET /website-rankings/general (EventController@general)
  WebsiteRankings --> WebsiteRankingsEventList: GET /website-rankings/events/list (EventController@list)
  WebsiteRankings --> WebsiteRankingsEventShow: GET /website-rankings/events/{event}/rankings (EventController@eventResult)
  WebsiteRankings --> WebsiteRankingsNation: GET /website-rankings/nation/{nation_id}/rankings (EventController@nation)

  PublicHomepage --> SchoolMap: GET /schools-map (SchoolController@schoolsMap)
  SchoolMap --> SchoolProfile: GET /school-profile/{school:slug} (SchoolController@detail)
  SchoolMap --> AcademyProfile: GET /academy-profile/{academy:slug} (AcademyController@detail)

  PublicHomepage --> CookiePolicy: GET /cookie-policy (static view)
  PublicHomepage --> PrivacyPolicy: GET /privacy-policy (static view)
```

## Role-scoped route packs

The controllers are shared; the role groups define access and route names. Below are the role-specific state machines.

### Technician routes

```mermaid
stateDiagram-v2
  [*] --> TechnicianUsers
  TechnicianUsers: GET /technician/users (PaginatedUserController@index)
  TechnicianUsers --> TechnicianUserFilter: GET /technician/users/filter (UserController@filter)
  TechnicianUsers --> TechnicianUserFilterResult: GET /technician/users/filter/result (UserController@filterResult)
  TechnicianUsers --> TechnicianUserSearch: GET /technician/users/search (UserController@search)
  TechnicianUsers --> TechnicianUserCreate: GET /technician/users/create (UserController@create)
  TechnicianUserCreate --> TechnicianUserStored: POST /technician/users (UserController@store)
  TechnicianUsers --> TechnicianUserEdit: GET /technician/users/{user} (UserController@edit)
  TechnicianUserEdit --> TechnicianUserUpdated: POST /technician/users/{user} (UserController@update)
  TechnicianUserEdit --> TechnicianUserDisabled: DELETE /technician/users/{user} (UserController@destroy)
  TechnicianUserEdit --> TechnicianUserPictureUpdated: PUT /technician/users/{user}/picture (UserController@picture)
  TechnicianUserEdit --> TechnicianUserLanguages: POST /technician/users/{user}/languages (UserController@languages)
  TechnicianUsers --> TechnicianNationAcademies: GET /technician/nation/{nation}/academies (NationController@academies)
  TechnicianUsers --> TechnicianAcademySchools: GET /technician/academy/{academy}/schools (AcademyController@schools)

  [*] --> TechnicianAcademies
  TechnicianAcademies: GET /technician/academies/all (AcademyController@all)
  TechnicianAcademies --> TechnicianAcademySearch: GET /technician/academies/search (AcademyController@search)

  [*] --> TechnicianSchools
  TechnicianSchools: GET /technician/schools/all (SchoolController@all)

  [*] --> TechnicianClans
  TechnicianClans: GET /technician/courses/all (ClanController@all)
  TechnicianClans --> TechnicianClansSearch: GET /technician/courses/search (ClanController@search)

  [*] --> TechnicianEvents
  TechnicianEvents: GET /technician/events (EventController@index)
  TechnicianEvents --> TechnicianEventsAll: GET /technician/events/all (EventController@all)
  TechnicianEvents --> TechnicianEventsSearch: GET /technician/events/search (EventController@search)
  TechnicianEvents --> TechnicianDashboardEvents: GET /technician/dashboard-events (EventController@dashboardEvents)
  TechnicianEvents --> TechnicianEventEdit: GET /technician/events/{event} (EventController@edit)
  TechnicianEventEdit --> TechnicianEventUpdated: POST /technician/events/{event} (EventController@update)
  TechnicianEventEdit --> TechnicianEventParticipants: GET /technician/events/{event}/participants (EventController@participants)
  TechnicianEventEdit --> TechnicianEventAvailable: GET /technician/events/{event}/available-users (EventController@available)
  TechnicianEventEdit --> TechnicianEventPersonnel: GET /technician/events/{event}/personnel (EventController@personnel)
  TechnicianEventEdit --> TechnicianEventParticipantsExport: GET /technician/events/{event}/participants/export (EventController@exportParticipants)
  TechnicianEventEdit --> TechnicianEventParticipantsAdd: POST /technician/add-participants (EventController@selectParticipants)
  TechnicianEvents --> TechnicianEventTypes: GET /technician/event-types/json (EventTypeController@list)

  [*] --> TechnicianImports
  TechnicianImports: GET /technician/imports (ImportController@index)
  TechnicianImports --> TechnicianImportCreate: GET /technician/imports/create (ImportController@create)
  TechnicianImportCreate --> TechnicianImportStored: POST /technician/imports (ImportController@store)
  TechnicianImports --> TechnicianImportUpdated: POST /technician/imports/{import} (ImportController@update)
  TechnicianImports --> TechnicianImportDownload: GET /technician/imports/{import}/download (ImportController@download)
  TechnicianImports --> TechnicianImportTemplate: GET /technician/imports/template (ImportController@template)

  [*] --> TechnicianExports
  TechnicianExports: GET /technician/exports (ExportController@index)
  TechnicianExports --> TechnicianExportCreate: GET /technician/exports/create (ExportController@create)
  TechnicianExportCreate --> TechnicianExportStored: POST /technician/exports (ExportController@store)
  TechnicianExports --> TechnicianExportUpdated: POST /technician/exports/{export} (ExportController@update)
  TechnicianExports --> TechnicianExportDownload: GET /technician/exports/{export}/download (ExportController@download)

  [*] --> TechnicianAnnouncements
  TechnicianAnnouncements: GET /technician/announcements (AnnouncementController@ownRoles)
  TechnicianAnnouncements --> TechnicianAnnouncementSeen: POST /technician/announcements/{announcement}/seen (AnnouncementController@setSeen)

  [*] --> TechnicianHelpers
  TechnicianHelpers: GET /technician/schools/academy (SchoolController@getByAcademy)
  TechnicianHelpers --> TechnicianClansBySchool: GET /technician/courses/school (ClanController@getBySchool)
```

### Athlete routes

```mermaid
stateDiagram-v2
  [*] --> AthleteAnnouncements
  AthleteAnnouncements: GET /athlete/announcements (AnnouncementController@ownRoles)
  AthleteAnnouncements --> AthleteAnnouncementSeen: POST /athlete/announcements/{announcement}/seen (AnnouncementController@setSeen)
```

### Instructor routes

```mermaid
stateDiagram-v2
  [*] --> InstructorUsers
  InstructorUsers: GET /instructor/users (PaginatedUserController@index)
  InstructorUsers --> InstructorUserFilter: GET /instructor/users/filter (UserController@filter)
  InstructorUsers --> InstructorUserFilterResult: GET /instructor/users/filter/result (UserController@filterResult)
  InstructorUsers --> InstructorUserSearch: GET /instructor/users/search (UserController@search)
  InstructorUsers --> InstructorUserCreate: GET /instructor/users/create (UserController@create)
  InstructorUserCreate --> InstructorUserStored: POST /instructor/users (UserController@store)
  InstructorUsers --> InstructorUserEdit: GET /instructor/users/{user} (UserController@edit)
  InstructorUserEdit --> InstructorUserUpdated: POST /instructor/users/{user} (UserController@update)
  InstructorUserEdit --> InstructorUserDisabled: DELETE /instructor/users/{user} (UserController@destroy)
  InstructorUserEdit --> InstructorUserPictureUpdated: PUT /instructor/users/{user}/picture (UserController@picture)
  InstructorUserEdit --> InstructorUserLanguages: POST /instructor/users/{user}/languages (UserController@languages)
  InstructorUsers --> InstructorNationAcademies: GET /instructor/nation/{nation}/academies (NationController@academies)
  InstructorUsers --> InstructorAcademySchools: GET /instructor/academy/{academy}/schools (AcademyController@schools)

  [*] --> InstructorEvents
  InstructorEvents: GET /instructor/events (EventController@index)
  InstructorEvents --> InstructorEventCreate: GET /instructor/events/create (EventController@create)
  InstructorEventCreate --> InstructorEventStored: POST /instructor/events/create (EventController@store)
  InstructorEvents --> InstructorEventEdit: GET /instructor/events/{event} (EventController@edit)
  InstructorEventEdit --> InstructorEventUpdated: POST /instructor/events/{event} (EventController@update)
  InstructorEventEdit --> InstructorEventDescriptionSaved: POST /instructor/events/{event}/description (EventController@saveDescription)
  InstructorEventEdit --> InstructorEventLocationSaved: POST /instructor/events/{event}/location (EventController@saveLocation)
  InstructorEventEdit --> InstructorEventThumbnailUpdated: PUT /instructor/events/{event}/thumbnail (EventController@updateThumbnail)
  InstructorEventEdit --> InstructorEventParticipants: GET /instructor/events/{event}/participants (EventController@participants)
  InstructorEventEdit --> InstructorEventAvailable: GET /instructor/events/{event}/available-users (EventController@available)
  InstructorEventEdit --> InstructorEventParticipantsAdd: POST /instructor/add-participants (EventController@selectParticipants)
  InstructorEventEdit --> InstructorEventParticipantsExport: GET /instructor/events/{event}/participants/export (EventController@exportParticipants)

  [*] --> InstructorClans
  InstructorClans: GET /instructor/courses (ClanController@index)
  InstructorClans --> InstructorClanCreate: GET /instructor/courses/create (ClanController@create)
  InstructorClanCreate --> InstructorClanStored: POST /instructor/courses (ClanController@store)
  InstructorClans --> InstructorClanEdit: GET /instructor/courses/{clan} (ClanController@edit)
  InstructorClanEdit --> InstructorClanUpdated: POST /instructor/courses/{clan} (ClanController@update)
  InstructorClanEdit --> InstructorClanDisabled: DELETE /instructor/courses/{clan} (ClanController@destroy)
  InstructorClanEdit --> InstructorClanUserCreated: POST /instructor/courses/{clan}/user/create (UserController@storeForClan)
  InstructorClanEdit --> InstructorClanInstructorAdded: POST /instructor/courses/{clan}/add-instructors (ClanController@addInstructor)
  InstructorClanEdit --> InstructorClanInstructorRemoved: POST /instructor/courses/{clan}/remove-instructors (ClanController@removeInstructor)
  InstructorClanEdit --> InstructorClanAthleteAdded: POST /instructor/courses/{clan}/add-athlete (ClanController@addAthlete)
  InstructorClanEdit --> InstructorClanAthleteRemoved: POST /instructor/courses/{clan}/remove-athlete (ClanController@removeAthlete)
  InstructorClans --> InstructorClansAll: GET /instructor/courses/all (ClanController@all)
  InstructorClans --> InstructorClansSearch: GET /instructor/courses/search (ClanController@search)
  InstructorClans --> InstructorClansBySchool: GET /instructor/courses/school (ClanController@getBySchool)

  [*] --> InstructorAnnouncements
  InstructorAnnouncements: GET /instructor/announcements (AnnouncementController@ownRoles)
  InstructorAnnouncements --> InstructorAnnouncementSeen: POST /instructor/announcements/{announcement}/seen (AnnouncementController@setSeen)

  [*] --> InstructorHelpers
  InstructorHelpers: GET /instructor/schools/academy (SchoolController@getByAcademy)
```

### Dean routes

```mermaid
stateDiagram-v2
  [*] --> DeanUsers
  DeanUsers: GET /dean/users (PaginatedUserController@index)
  DeanUsers --> DeanUsersFiltered: GET /dean/filtered-by-dashboard (PaginatedUserController@usersFilteredByActiveAndCoursePagination)
  DeanUsers --> DeanUserFilter: GET /dean/users/filter (UserController@filter)
  DeanUsers --> DeanUserFilterResult: GET /dean/users/filter/result (UserController@filterResult)
  DeanUsers --> DeanUserSearch: GET /dean/users/search (UserController@search)
  DeanUsers --> DeanUserCreate: GET /dean/users/create (UserController@create)
  DeanUserCreate --> DeanUserStored: POST /dean/users (UserController@store)
  DeanUsers --> DeanUserEdit: GET /dean/users/{user} (UserController@edit)
  DeanUserEdit --> DeanUserUpdated: POST /dean/users/{user} (UserController@update)
  DeanUserEdit --> DeanUserDisabled: DELETE /dean/users/{user} (UserController@destroy)
  DeanUserEdit --> DeanUserRolesUpdated: POST /dean/user-roles/{user} (UserController@updateRoles)
  DeanUserEdit --> DeanUserPictureUpdated: PUT /dean/users/{user}/picture (UserController@picture)
  DeanUserEdit --> DeanWeaponFormsAthlete: POST /dean/users/{user}/weapon-forms-athlete (UserController@editWeaponFormsAthlete)
  DeanUserEdit --> DeanWeaponFormsDate: POST /dean/users/{user}/weapon-forms-edit-date (UserController@editWeaponFormsAwardingDate)

  [*] --> DeanRoles
  DeanRoles: GET /dean/custom-roles (RoleController@index)
  DeanRoles --> DeanRolesSearch: GET /dean/custom-roles/search (RoleController@search)
  DeanRoles --> DeanRolesAssign: POST /dean/custom-roles/assign (RoleController@assign)
  DeanRoles --> DeanRolesStored: POST /dean/custom-roles (RoleController@store)

  [*] --> DeanAcademies
  DeanAcademies: GET /dean/academies/all (AcademyController@all)
  DeanAcademies --> DeanAcademiesSearch: GET /dean/academies/search (AcademyController@search)

  [*] --> DeanSchools
  DeanSchools: GET /dean/school (SchoolController@index)
  DeanSchools --> DeanSchoolsAll: GET /dean/schools/all (SchoolController@all)
  DeanSchools --> DeanSchoolsByAcademy: GET /dean/schools/academy (SchoolController@getByAcademy)
  DeanSchools --> DeanSchoolEdit: GET /dean/schools/{school} (SchoolController@edit)
  DeanSchoolEdit --> DeanSchoolUpdated: POST /dean/schools/{school} (SchoolController@update)
  DeanSchoolEdit --> DeanSchoolUsersCreated: POST /dean/schools/{school}/users/create (UserController@storeForSchool)
  DeanSchoolEdit --> DeanSchoolClanCreated: POST /dean/schools/{school}/clan/create (ClanController@storeForSchool)
  DeanSchoolEdit --> DeanSchoolClanAdded: POST /dean/schools/{school}/clans (SchoolController@addClan)
  DeanSchoolEdit --> DeanSchoolPersonnelAdded: POST /dean/schools/{school}/add-personnel (SchoolController@addPersonnel)
  DeanSchoolEdit --> DeanSchoolPersonnelRemoved: POST /dean/schools/{school}/remove-personnel (SchoolController@removePersonnel)
  DeanSchoolEdit --> DeanSchoolAthleteAdded: POST /dean/schools/{school}/athlete (SchoolController@addAthlete)
  DeanSchoolEdit --> DeanSchoolAthleteRemoved: POST /dean/schools/{school}/remove-athlete (SchoolController@removeAthlete)

  [*] --> DeanClans
  DeanClans: GET /dean/courses (ClanController@index)
  DeanClans --> DeanClansSchool: GET /dean/courses/school (ClanController@getBySchool)
  DeanClans --> DeanClansAll: GET /dean/courses/all (ClanController@all)
  DeanClans --> DeanClansSearch: GET /dean/courses/search (ClanController@search)
  DeanClans --> DeanClanCreate: GET /dean/courses/create (ClanController@create)
  DeanClanCreate --> DeanClanStored: POST /dean/courses (ClanController@store)
  DeanClans --> DeanClanEdit: GET /dean/courses/{clan} (ClanController@edit)
  DeanClanEdit --> DeanClanUpdated: POST /dean/courses/{clan} (ClanController@update)
  DeanClanEdit --> DeanClanDisabled: DELETE /dean/courses/{clan} (ClanController@destroy)
  DeanClanEdit --> DeanClanUserCreated: POST /dean/courses/{clan}/user/create (UserController@storeForClan)
  DeanClanEdit --> DeanClanInstructorAdded: POST /dean/courses/{clan}/add-instructors (ClanController@addInstructor)
  DeanClanEdit --> DeanClanInstructorRemoved: POST /dean/courses/{clan}/remove-instructors (ClanController@removeInstructor)
  DeanClanEdit --> DeanClanAthleteAdded: POST /dean/courses/{clan}/add-athlete (ClanController@addAthlete)
  DeanClanEdit --> DeanClanAthleteRemoved: POST /dean/courses/{clan}/remove-athlete (ClanController@removeAthlete)

  [*] --> DeanEvents
  DeanEvents: GET /dean/events (EventController@index)
  DeanEvents --> DeanEventsAll: GET /dean/events/all (EventController@all)
  DeanEvents --> DeanEventsSearch: GET /dean/events/search (EventController@search)
  DeanEvents --> DeanEventsCalendar: GET /dean/events/calendar (EventController@calendar)
  DeanEvents --> DeanEventCreate: GET /dean/events/create (EventController@create)
  DeanEventCreate --> DeanEventStored: POST /dean/events (EventController@store)
  DeanEvents --> DeanEventEdit: GET /dean/events/{event} (EventController@edit)
  DeanEventEdit --> DeanEventParticipants: GET /dean/events/{event}/participants (EventController@participants)
  DeanEventEdit --> DeanEventAvailable: GET /dean/events/{event}/available-users (EventController@available)
  DeanEventEdit --> DeanEventParticipantsAdd: POST /dean/add-participants (EventController@selectParticipants)
  DeanEventEdit --> DeanEventParticipantsExport: GET /dean/events/{event}/participants/export (EventController@exportParticipants)
  DeanEvents --> DeanEventTypes: GET /dean/event-types/json (EventTypeController@list)
  DeanEventEdit --> DeanEventPersonnel: GET /dean/events/{event}/personnel (EventController@personnel)

  [*] --> DeanImports
  DeanImports: GET /dean/imports (ImportController@index)
  DeanImports --> DeanImportCreate: GET /dean/imports/create (ImportController@create)
  DeanImportCreate --> DeanImportStored: POST /dean/imports (ImportController@store)
  DeanImports --> DeanImportUpdated: POST /dean/imports/{import} (ImportController@update)
  DeanImports --> DeanImportDownload: GET /dean/imports/{import}/download (ImportController@download)
  DeanImports --> DeanImportTemplate: GET /dean/imports/template (ImportController@template)

  [*] --> DeanExports
  DeanExports: GET /dean/exports (ExportController@index)
  DeanExports --> DeanExportCreate: GET /dean/exports/create (ExportController@create)
  DeanExportCreate --> DeanExportStored: POST /dean/exports (ExportController@store)
  DeanExports --> DeanExportUpdated: POST /dean/exports/{export} (ExportController@update)
  DeanExports --> DeanExportDownload: GET /dean/exports/{export}/download (ExportController@download)

  [*] --> DeanAnnouncements
  DeanAnnouncements: GET /dean/announcements (AnnouncementController@ownRoles)
  DeanAnnouncements --> DeanAnnouncementSeen: POST /dean/announcements/{announcement}/seen (AnnouncementController@setSeen)
```

### Manager routes

```mermaid
stateDiagram-v2
  [*] --> ManagerFees
  ManagerFees: GET /manager/fees (FeeController@index)
  ManagerFees --> ManagerFeesEstimate: GET /manager/fees/extimate (FeeController@extimateFeeConsumption)
  ManagerFees --> ManagerFeesAssociate: POST /manager/fees/associate (FeeController@associateFeesToUsers)

  [*] --> ManagerUsers
  ManagerUsers: GET /manager/users (PaginatedUserController@index)
  ManagerUsers --> ManagerUsersFiltered: GET /manager/filtered-by-dashboard (PaginatedUserController@usersFilteredByActiveAndCoursePagination)
  ManagerUsers --> ManagerUserFilter: GET /manager/users/filter (UserController@filter)
  ManagerUsers --> ManagerUserFilterResult: GET /manager/users/filter/result (UserController@filterResult)
  ManagerUsers --> ManagerUserSearch: GET /manager/users/search (UserController@search)
  ManagerUsers --> ManagerUserCreate: GET /manager/users/create (UserController@create)
  ManagerUserCreate --> ManagerUserStored: POST /manager/users (UserController@store)
  ManagerUsers --> ManagerUserEdit: GET /manager/users/{user} (UserController@edit)
  ManagerUserEdit --> ManagerUserUpdated: POST /manager/users/{user} (UserController@update)
  ManagerUserEdit --> ManagerUserDisabled: DELETE /manager/users/{user} (UserController@destroy)
  ManagerUserEdit --> ManagerUserRolesUpdated: POST /manager/user-roles/{user} (UserController@updateRoles)
  ManagerUserEdit --> ManagerUserPictureUpdated: PUT /manager/users/{user}/picture (UserController@picture)
  ManagerUserEdit --> ManagerWeaponFormsAthlete: POST /manager/users/{user}/weapon-forms-athlete (UserController@editWeaponFormsAthlete)
  ManagerUserEdit --> ManagerWeaponFormsDate: POST /manager/users/{user}/weapon-forms-edit-date (UserController@editWeaponFormsAwardingDate)
  ManagerUsers --> ManagerAssociateSchool: POST /manager/users/associate-school (UserController@associateSchool)
  ManagerUsers --> ManagerRemoveSchool: POST /manager/users/remove-school (UserController@removeSchool)
  ManagerUsers --> ManagerSetMainInstitution: POST /manager/users/set-main-institution (UserController@setMainInstitution)

  [*] --> ManagerRoles
  ManagerRoles: GET /manager/custom-roles (RoleController@index)
  ManagerRoles --> ManagerRolesSearch: GET /manager/custom-roles/search (RoleController@search)
  ManagerRoles --> ManagerRolesAssign: POST /manager/custom-roles/assign (RoleController@assign)
  ManagerRoles --> ManagerRolesStored: POST /manager/custom-roles (RoleController@store)

  [*] --> ManagerAcademies
  ManagerAcademies: GET /manager/academy (AcademyController@index)
  ManagerAcademies --> ManagerAcademiesAll: GET /manager/academies/all (AcademyController@all)
  ManagerAcademies --> ManagerAcademiesSearch: GET /manager/academies/search (AcademyController@search)
  ManagerAcademies --> ManagerAcademyEdit: GET /manager/academies/{academy} (AcademyController@edit)
  ManagerAcademyEdit --> ManagerAcademyUsersCreated: POST /manager/academies/{academy}/users/create (UserController@storeForAcademy)
  ManagerAcademyEdit --> ManagerAcademyPersonnelAdded: POST /manager/academies/{academy}/add-personnel (AcademyController@addPersonnel)
  ManagerAcademyEdit --> ManagerAcademyPersonnelRemoved: POST /manager/academies/{academy}/remove-personnel (AcademyController@removePersonnel)
  ManagerAcademyEdit --> ManagerAcademyAthleteAdded: POST /manager/academies/{academy}/add-athlete (AcademyController@addAthlete)
  ManagerAcademyEdit --> ManagerAcademyAthleteRemoved: POST /manager/academies/{academy}/remove-athlete (AcademyController@removeAthlete)
  ManagerAcademies --> ManagerAcademyUsersSearch: GET /manager/academies/{academy}/users-search (AcademyController@searchUsers)

  [*] --> ManagerSchools
  ManagerSchools: GET /manager/school (SchoolController@index)
  ManagerSchools --> ManagerSchoolsAll: GET /manager/schools/all (SchoolController@all)
  ManagerSchools --> ManagerSchoolsByAcademy: GET /manager/schools/academy (SchoolController@getByAcademy)
  ManagerSchools --> ManagerSchoolsIndex: GET /manager/schools (SchoolController@index)
  ManagerSchools --> ManagerSchoolsSearch: GET /manager/schools/search (SchoolController@search)
  ManagerSchools --> ManagerSchoolEdit: GET /manager/schools/{school} (SchoolController@edit)
  ManagerSchoolEdit --> ManagerSchoolUpdated: POST /manager/schools/{school} (SchoolController@update)
  ManagerSchoolEdit --> ManagerSchoolUsersCreated: POST /manager/schools/{school}/users/create (UserController@storeForSchool)
  ManagerSchoolEdit --> ManagerSchoolClanCreated: POST /manager/schools/{school}/clan/create (ClanController@storeForSchool)
  ManagerSchoolEdit --> ManagerSchoolClanAdded: POST /manager/schools/{school}/clans (SchoolController@addClan)
  ManagerSchoolEdit --> ManagerSchoolPersonnelAdded: POST /manager/schools/{school}/add-personnel (SchoolController@addPersonnel)
  ManagerSchoolEdit --> ManagerSchoolPersonnelRemoved: POST /manager/schools/{school}/remove-personnel (SchoolController@removePersonnel)
  ManagerSchoolEdit --> ManagerSchoolAthleteAdded: POST /manager/schools/{school}/athlete (SchoolController@addAthlete)
  ManagerSchoolEdit --> ManagerSchoolAthleteRemoved: POST /manager/schools/{school}/remove-athlete (SchoolController@removeAthlete)

  [*] --> ManagerClans
  ManagerClans: GET /manager/courses (ClanController@index)
  ManagerClans --> ManagerClansSchool: GET /manager/courses/school (ClanController@getBySchool)
  ManagerClans --> ManagerClansAll: GET /manager/courses/all (ClanController@all)
  ManagerClans --> ManagerClansSearch: GET /manager/courses/search (ClanController@search)
  ManagerClans --> ManagerClanCreate: GET /manager/courses/create (ClanController@create)
  ManagerClanCreate --> ManagerClanStored: POST /manager/courses (ClanController@store)
  ManagerClans --> ManagerClanEdit: GET /manager/courses/{clan} (ClanController@edit)
  ManagerClanEdit --> ManagerClanUpdated: POST /manager/courses/{clan} (ClanController@update)
  ManagerClanEdit --> ManagerClanDisabled: DELETE /manager/courses/{clan} (ClanController@destroy)
  ManagerClanEdit --> ManagerClanUserCreated: POST /manager/courses/{clan}/user/create (UserController@storeForClan)
  ManagerClanEdit --> ManagerClanInstructorAdded: POST /manager/courses/{clan}/add-instructors (ClanController@addInstructor)
  ManagerClanEdit --> ManagerClanInstructorRemoved: POST /manager/courses/{clan}/remove-instructors (ClanController@removeInstructor)
  ManagerClanEdit --> ManagerClanAthleteAdded: POST /manager/courses/{clan}/add-athlete (ClanController@addAthlete)
  ManagerClanEdit --> ManagerClanAthleteRemoved: POST /manager/courses/{clan}/remove-athlete (ClanController@removeAthlete)

  [*] --> ManagerEvents
  ManagerEvents: GET /manager/events (EventController@index)
  ManagerEvents --> ManagerEventsAll: GET /manager/events/all (EventController@all)
  ManagerEvents --> ManagerEventsSearch: GET /manager/events/search (EventController@search)
  ManagerEvents --> ManagerEventsCalendar: GET /manager/events/calendar (EventController@calendar)
  ManagerEvents --> ManagerEventCreate: GET /manager/events/create (EventController@create)
  ManagerEventCreate --> ManagerEventStored: POST /manager/events (EventController@store)
  ManagerEvents --> ManagerEventEdit: GET /manager/events/{event} (EventController@edit)
  ManagerEventEdit --> ManagerEventUpdated: POST /manager/events/{event} (EventController@update)
  ManagerEventEdit --> ManagerEventDescriptionSaved: POST /manager/events/{event}/description (EventController@saveDescription)
  ManagerEventEdit --> ManagerEventLocationSaved: POST /manager/events/{event}/location (EventController@saveLocation)
  ManagerEventEdit --> ManagerEventThumbnailUpdated: PUT /manager/events/{event}/thumbnail (EventController@updateThumbnail)
  ManagerEventEdit --> ManagerEventParticipants: GET /manager/events/{event}/participants (EventController@participants)
  ManagerEventEdit --> ManagerEventAvailable: GET /manager/events/{event}/available-users (EventController@available)
  ManagerEventEdit --> ManagerEventParticipantsAdd: POST /manager/add-participants (EventController@selectParticipants)
  ManagerEventEdit --> ManagerEventParticipantsExport: GET /manager/events/{event}/participants/export (EventController@exportParticipants)
  ManagerEvents --> ManagerEventTypes: GET /manager/event-types/json (EventTypeController@list)
  ManagerEventEdit --> ManagerEventAvailablePersonnel: GET /manager/events/{event}/available-personnel (EventController@availablePersonnel)
  ManagerEventEdit --> ManagerEventPersonnel: GET /manager/events/{event}/personnel (EventController@personnel)
  ManagerEventEdit --> ManagerEventPersonnelAdded: POST /manager/events/{event}/add-personnel (EventController@addPersonnel)

  [*] --> ManagerImports
  ManagerImports: GET /manager/imports (ImportController@index)
  ManagerImports --> ManagerImportCreate: GET /manager/imports/create (ImportController@create)
  ManagerImportCreate --> ManagerImportStored: POST /manager/imports (ImportController@store)
  ManagerImports --> ManagerImportUpdated: POST /manager/imports/{import} (ImportController@update)
  ManagerImports --> ManagerImportDownload: GET /manager/imports/{import}/download (ImportController@download)
  ManagerImports --> ManagerImportTemplate: GET /manager/imports/template (ImportController@template)

  [*] --> ManagerExports
  ManagerExports: GET /manager/exports (ExportController@index)
  ManagerExports --> ManagerExportCreate: GET /manager/exports/create (ExportController@create)
  ManagerExportCreate --> ManagerExportStored: POST /manager/exports (ExportController@store)
  ManagerExports --> ManagerExportUpdated: POST /manager/exports/{export} (ExportController@update)
  ManagerExports --> ManagerExportDownload: GET /manager/exports/{export}/download (ExportController@download)

  [*] --> ManagerAnnouncements
  ManagerAnnouncements: GET /manager/announcements (AnnouncementController@ownRoles)
  ManagerAnnouncements --> ManagerAnnouncementSeen: POST /manager/announcements/{announcement}/seen (AnnouncementController@setSeen)
```

### Rector routes

```mermaid
stateDiagram-v2
  [*] --> RectorFees
  RectorFees: GET /rector/fees (FeeController@index)
  RectorFees --> RectorFeesPurchase: GET /rector/fees/purchase (FeeController@create)
  RectorFees --> RectorFeesRenew: GET /rector/fees/renew (FeeController@renew)
  RectorFees --> RectorFeesStripeCheckout: GET /rector/fees/stripe/checkout (FeeController@checkoutStripe)
  RectorFees --> RectorFeesSuccess: GET /rector/fees/success (FeeController@success)
  RectorFees --> RectorFeesCancel: GET /rector/fees/cancel (FeeController@cancel)
  RectorFees --> RectorFeesPaypalCheckout: POST /rector/fees/paypal/checkout (FeeController@checkoutPaypal)
  RectorFees --> RectorFeesPaypalSuccess: GET /rector/fees/paypal/success (FeeController@successPaypal)
  RectorFees --> RectorFeesPaypalCancel: GET /rector/fees/paypal/cancel (FeeController@cancelPaypal)
  RectorFees --> RectorFeesEstimate: GET /rector/fees/extimate (FeeController@extimateFeeConsumption)
  RectorFees --> RectorFeesAssociate: POST /rector/fees/associate (FeeController@associateFeesToUsers)
  RectorFees --> RectorUserInvoices: GET /rector/invoices/user-data/{user} (UserController@invoiceData)

  [*] --> RectorUsers
  RectorUsers: GET /rector/users (PaginatedUserController@index)
  RectorUsers --> RectorUsersFiltered: GET /rector/filtered-by-dashboard (PaginatedUserController@usersFilteredByActiveAndCoursePagination)
  RectorUsers --> RectorUserFilter: GET /rector/users/filter (UserController@filter)
  RectorUsers --> RectorUserFilterResult: GET /rector/users/filter/result (UserController@filterResult)
  RectorUsers --> RectorUserSearch: GET /rector/users/search (UserController@search)
  RectorUsers --> RectorUserCreate: GET /rector/users/create (UserController@create)
  RectorUserCreate --> RectorUserStored: POST /rector/users (UserController@store)
  RectorUsers --> RectorUserEdit: GET /rector/users/{user} (UserController@edit)
  RectorUserEdit --> RectorUserUpdated: POST /rector/users/{user} (UserController@update)
  RectorUserEdit --> RectorUserDisabled: DELETE /rector/users/{user} (UserController@destroy)
  RectorUserEdit --> RectorUserRolesUpdated: POST /rector/user-roles/{user} (UserController@updateRoles)
  RectorUserEdit --> RectorUserPictureUpdated: PUT /rector/users/{user}/picture (UserController@picture)
  RectorUsers --> RectorNationAcademies: GET /rector/nation/{nation}/academies (NationController@academies)
  RectorUserEdit --> RectorWeaponFormsAthlete: POST /rector/users/{user}/weapon-forms-athlete (UserController@editWeaponFormsAthlete)
  RectorUserEdit --> RectorWeaponFormsDate: POST /rector/users/{user}/weapon-forms-edit-date (UserController@editWeaponFormsAwardingDate)
  RectorUsers --> RectorAssociateSchool: POST /rector/users/associate-school (UserController@associateSchool)
  RectorUsers --> RectorRemoveSchool: POST /rector/users/remove-school (UserController@removeSchool)
  RectorUsers --> RectorSetMainInstitution: POST /rector/users/set-main-institution (UserController@setMainInstitution)

  [*] --> RectorRoles
  RectorRoles: GET /rector/custom-roles (RoleController@index)
  RectorRoles --> RectorRolesSearch: GET /rector/custom-roles/search (RoleController@search)
  RectorRoles --> RectorRolesAssign: POST /rector/custom-roles/assign (RoleController@assign)
  RectorRoles --> RectorRolesStored: POST /rector/custom-roles (RoleController@store)

  [*] --> RectorAcademies
  RectorAcademies: GET /rector/academies (AcademyController@index)
  RectorAcademies --> RectorAcademiesAll: GET /rector/academies/all (AcademyController@all)
  RectorAcademies --> RectorAcademiesSearch: GET /rector/academies/search (AcademyController@search)
  RectorAcademies --> RectorAcademyEdit: GET /rector/academies/{academy} (AcademyController@edit)
  RectorAcademyEdit --> RectorAcademySchoolsCreate: POST /rector/academies/{academy}/schools/create (SchoolController@storeacademy)
  RectorAcademyEdit --> RectorAcademySchoolsAdd: POST /rector/academies/{academy}/schools (AcademyController@addSchool)
  RectorAcademyEdit --> RectorAcademyUsersCreated: POST /rector/academies/{academy}/users/create (UserController@storeForAcademy)
  RectorAcademyEdit --> RectorAcademyPersonnelAdded: POST /rector/academies/{academy}/add-personnel (AcademyController@addPersonnel)
  RectorAcademyEdit --> RectorAcademyPersonnelRemoved: POST /rector/academies/{academy}/remove-personnel (AcademyController@removePersonnel)
  RectorAcademyEdit --> RectorAcademyAthleteAdded: POST /rector/academies/{academy}/add-athlete (AcademyController@addAthlete)
  RectorAcademyEdit --> RectorAcademyAthleteRemoved: POST /rector/academies/{academy}/remove-athlete (AcademyController@removeAthlete)
  RectorAcademies --> RectorAcademyUsersSearch: GET /rector/academies/{academy}/users-search (AcademyController@searchUsers)

  [*] --> RectorSchools
  RectorSchools: GET /rector/schools (SchoolController@index)
  RectorSchools --> RectorSchoolsAll: GET /rector/schools/all (SchoolController@all)
  RectorSchools --> RectorSchoolsByAcademy: GET /rector/schools/academy (SchoolController@getByAcademy)
  RectorSchools --> RectorSchoolsCreate: GET /rector/schools/create (SchoolController@create)
  RectorSchools --> RectorSchoolsSearch: GET /rector/schools/search (SchoolController@search)
  RectorSchools --> RectorSchoolEdit: GET /rector/schools/{school} (SchoolController@edit)
  RectorSchoolEdit --> RectorSchoolUpdated: POST /rector/schools/{school} (SchoolController@update)
  RectorSchoolEdit --> RectorSchoolDisabled: DELETE /rector/schools/{school} (SchoolController@destroy)
  RectorSchools --> RectorSchoolStored: POST /rector/schools (SchoolController@store)
  RectorSchoolEdit --> RectorSchoolUsersCreated: POST /rector/schools/{school}/users/create (UserController@storeForSchool)
  RectorSchoolEdit --> RectorSchoolClanCreated: POST /rector/schools/{school}/clan/create (ClanController@storeForSchool)
  RectorSchoolEdit --> RectorSchoolClanAdded: POST /rector/schools/{school}/clans (SchoolController@addClan)
  RectorSchoolEdit --> RectorSchoolPersonnelAdded: POST /rector/schools/{school}/add-personnel (SchoolController@addPersonnel)
  RectorSchoolEdit --> RectorSchoolPersonnelRemoved: POST /rector/schools/{school}/remove-personnel (SchoolController@removePersonnel)
  RectorSchoolEdit --> RectorSchoolAthleteAdded: POST /rector/schools/{school}/athlete (SchoolController@addAthlete)
  RectorSchoolEdit --> RectorSchoolAthleteRemoved: POST /rector/schools/{school}/remove-athlete (SchoolController@removeAthlete)
  RectorSchoolEdit --> RectorSchoolUsersSearch: GET /rector/schools/{school}/users-search (SchoolController@searchUsers)

  [*] --> RectorClans
  RectorClans: GET /rector/courses (ClanController@index)
  RectorClans --> RectorClansSchool: GET /rector/courses/school (ClanController@getBySchool)
  RectorClans --> RectorClansAll: GET /rector/courses/all (ClanController@all)
  RectorClans --> RectorClansSearch: GET /rector/courses/search (ClanController@search)
  RectorClans --> RectorClanCreate: GET /rector/courses/create (ClanController@create)
  RectorClanCreate --> RectorClanStored: POST /rector/courses (ClanController@store)
  RectorClans --> RectorClanEdit: GET /rector/courses/{clan} (ClanController@edit)
  RectorClanEdit --> RectorClanUpdated: POST /rector/courses/{clan} (ClanController@update)
  RectorClanEdit --> RectorClanDisabled: DELETE /rector/courses/{clan} (ClanController@destroy)
  RectorClanEdit --> RectorClanUserCreated: POST /rector/courses/{clan}/user/create (UserController@storeForClan)
  RectorClanEdit --> RectorClanInstructorAdded: POST /rector/courses/{clan}/add-instructors (ClanController@addInstructor)
  RectorClanEdit --> RectorClanInstructorRemoved: POST /rector/courses/{clan}/remove-instructors (ClanController@removeInstructor)
  RectorClanEdit --> RectorClanAthleteAdded: POST /rector/courses/{clan}/add-athlete (ClanController@addAthlete)
  RectorClanEdit --> RectorClanAthleteRemoved: POST /rector/courses/{clan}/remove-athlete (ClanController@removeAthlete)

  [*] --> RectorEvents
  RectorEvents: GET /rector/events (EventController@index)
  RectorEvents --> RectorEventsAll: GET /rector/events/all (EventController@all)
  RectorEvents --> RectorEventsSearch: GET /rector/events/search (EventController@search)
  RectorEvents --> RectorEventsCalendar: GET /rector/events/calendar (EventController@calendar)
  RectorEvents --> RectorEventCreate: GET /rector/events/create (EventController@create)
  RectorEventCreate --> RectorEventStored: POST /rector/events (EventController@store)
  RectorEvents --> RectorEventEdit: GET /rector/events/{event} (EventController@edit)
  RectorEventEdit --> RectorEventUpdated: POST /rector/events/{event} (EventController@update)
  RectorEventEdit --> RectorEventDescriptionSaved: POST /rector/events/{event}/description (EventController@saveDescription)
  RectorEventEdit --> RectorEventLocationSaved: POST /rector/events/{event}/location (EventController@saveLocation)
  RectorEventEdit --> RectorEventThumbnailUpdated: PUT /rector/events/{event}/thumbnail (EventController@updateThumbnail)
  RectorEventEdit --> RectorEventParticipants: GET /rector/events/{event}/participants (EventController@participants)
  RectorEventEdit --> RectorEventAvailable: GET /rector/events/{event}/available-users (EventController@available)
  RectorEventEdit --> RectorEventParticipantsAdd: POST /rector/add-participants (EventController@selectParticipants)
  RectorEventEdit --> RectorEventParticipantsExport: GET /rector/events/{event}/participants/export (EventController@exportParticipants)
  RectorEvents --> RectorEventTypes: GET /rector/event-types/json (EventTypeController@list)
  RectorEventEdit --> RectorEventAvailablePersonnel: GET /rector/events/{event}/available-personnel (EventController@availablePersonnel)
  RectorEventEdit --> RectorEventPersonnel: GET /rector/events/{event}/personnel (EventController@personnel)
  RectorEventEdit --> RectorEventPersonnelAdded: POST /rector/events/{event}/add-personnel (EventController@addPersonnel)

  [*] --> RectorImports
  RectorImports: GET /rector/imports (ImportController@index)
  RectorImports --> RectorImportCreate: GET /rector/imports/create (ImportController@create)
  RectorImportCreate --> RectorImportStored: POST /rector/imports (ImportController@store)
  RectorImports --> RectorImportUpdated: POST /rector/imports/{import} (ImportController@update)
  RectorImports --> RectorImportDownload: GET /rector/imports/{import}/download (ImportController@download)
  RectorImports --> RectorImportTemplate: GET /rector/imports/template (ImportController@template)

  [*] --> RectorExports
  RectorExports: GET /rector/exports (ExportController@index)
  RectorExports --> RectorExportCreate: GET /rector/exports/create (ExportController@create)
  RectorExportCreate --> RectorExportStored: POST /rector/exports (ExportController@store)
  RectorExports --> RectorExportUpdated: POST /rector/exports/{export} (ExportController@update)
  RectorExports --> RectorExportDownload: GET /rector/exports/{export}/download (ExportController@download)

  [*] --> RectorAnnouncements
  RectorAnnouncements: GET /rector/announcements (AnnouncementController@ownRoles)
  RectorAnnouncements --> RectorAnnouncementSeen: POST /rector/announcements/{announcement}/seen (AnnouncementController@setSeen)
```
