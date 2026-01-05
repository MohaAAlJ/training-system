**Overview**

This README documents the Filament admin panel portion of the app: Resource classes, Forms, Tables, Widgets, authorization/policies, and customization points for the admin UI.

**Where to look**

-   Filament resources and pages: `app/Filament` (Resources, Pages, Widgets, Auth)
-   Resource models: `app/Models` (e.g., `Trainee`, `Application`, `Institution`)
-   Policies: `app/Policies`

**Resources & structure**

-   Each Filament resource typically contains:
    -   `Resource.php` (navigation and registration)
    -   `Pages` (List, Create, Edit)
    -   `Resource/RelationManagers` (if used)
    -   Form and Table definitions inside the Resource and Page classes

**Forms & fields**

-   Forms define fields shown in the create/edit pages (text, selects, relation fields, toggles). Check the `form()` definitions inside resource classes.
-   Relation fields and custom field widgets are used for associations (belongsTo, hasMany).

**Tables & actions**

-   Table columns and filters are declared in resource table definitions.
-   Actions (bulk actions, per-row actions) live in resource or page definitions and may trigger model changes or navigation.

**Authorization & policies**

-   Filament respects model policies; check `app/Policies` for `ApplicationPolicy`, `InstitutionPolicy`, etc.
-   Additional Filament permissions and role gating may be configured in the resources.

**Widgets & custom pages**

-   Check `app/Filament/Widgets` and `app/Filament/Pages` for custom dashboards or admin utilities.

**Extending & hooks**

-   Use resource hooks (before/after save) to run custom logic.
-   Override queries for index/listing to scope results based on user or institution.

**Where to change behavior**

-   Modify resource classes under `app/Filament/Resources` to change forms, tables, or actions.
-   Add/update policies in `app/Policies` to control access.
-   Add widgets/pages for custom admin UIs under `app/Filament`.

**Optional: generate repo-specific mapping**

I can scan `app/Filament` and produce a list of each Resource with its fields, columns, relation managers, and the policies that control it.
