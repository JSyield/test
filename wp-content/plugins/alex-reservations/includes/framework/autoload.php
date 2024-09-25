<?php

// Application constants
include 'app-constants.php';
include 'app-functions.php';



// Framework constants from this plugin ALEXR
const EVAVEL_FRAMEWORK = EVAVEL_PLUGIN_DIR . 'includes/framework/src/Evavel/';

// Service Provider
require_once EVAVEL_FRAMEWORK . 'Container/EvaContainer.php';
require_once EVAVEL_FRAMEWORK . 'Eva.php';

require_once EVAVEL_FRAMEWORK . 'Providers/ServiceProvider.php';
require_once EVAVEL_FRAMEWORK . 'Providers/EventServiceProvider.php';
require_once EVAVEL_FRAMEWORK . 'Providers/AuthServiceProvider.php';

// Facades
require_once EVAVEL_FRAMEWORK . 'Facades/Facade.php';
require_once EVAVEL_FRAMEWORK . 'Facades/Gate.php';

// Gate
require_once EVAVEL_FRAMEWORK . 'Auth/Gate.php';

// Events
require_once EVAVEL_FRAMEWORK . 'Events/Traits/Dispatcher.php';
require_once EVAVEL_FRAMEWORK . 'Events/Dispatcher.php';

// Ejemplos.. @todo quitar
//require_once EVAVEL_FRAMEWORK . 'Events/ExampleListener.php';

// Helpers
require_once EVAVEL_FRAMEWORK . 'helpers/wordpress.php';
require_once EVAVEL_FRAMEWORK . 'helpers/general.php';
require_once EVAVEL_FRAMEWORK . 'helpers/settings.php';
require_once EVAVEL_FRAMEWORK . 'helpers/routes.php';
require_once EVAVEL_FRAMEWORK . 'helpers/models.php';
require_once EVAVEL_FRAMEWORK . 'helpers/datetime.php';
require_once EVAVEL_FRAMEWORK . 'helpers/translate.php';

// Interfaces
require_once EVAVEL_FRAMEWORK . 'Interfaces/ToJsonSerialize.php';
require_once EVAVEL_FRAMEWORK . 'Interfaces/Authorizable.php';
require_once EVAVEL_FRAMEWORK . 'Interfaces/Arrayable.php';


// Support
require_once EVAVEL_FRAMEWORK . 'Support/Stringable.php';
require_once EVAVEL_FRAMEWORK . 'Support/Str.php';

// DB
require_once EVAVEL_FRAMEWORK . 'Database/DB.php';

// Routes
require_once EVAVEL_FRAMEWORK . 'Enums/Context.php';
require_once EVAVEL_FRAMEWORK . 'Http/Route.php';
require_once EVAVEL_FRAMEWORK . 'Http/RegisterRoutes.php';

require_once EVAVEL_FRAMEWORK . 'Http/ResolveUser.php';
require_once EVAVEL_FRAMEWORK . 'Http/ResolveRoute.php';

// Pipeline
require_once EVAVEL_FRAMEWORK . 'Pipeline/Pipeline.php';
require_once EVAVEL_FRAMEWORK . 'Pipeline/ExampleMiddleware.php';

// Controllers
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/Traits/ManageSettings.php';

require_once EVAVEL_FRAMEWORK . 'Http/Controllers/Controller.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/ApplicationController.php';
//require_once EVAVEL_FRAMEWORK . 'Http/Controllers/DevToolsController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/ResourceIndexController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/ResourceDetailController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/ResourceSettingsController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/ResourceUpdateFieldsController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/ResourceUpdateController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/ResourceUpdateBulkController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/ResourceCreationFieldsController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/ResourceCreateController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/ResourceDestroyController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/ResourceDestroyBulkController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/AssociatableController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/FilterController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/ActionController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/LensFilterController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/LensActionController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/SettingsController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/UserSettingsController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/AppSettingsController.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/AppHeartBeatController.php';


// Resource Pages
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/LensIndexController.php';

// Query
require_once EVAVEL_FRAMEWORK . 'Query/Connections/Wordpress.php';
require_once EVAVEL_FRAMEWORK . 'Query/Traits/WhereHasTrait.php';
require_once EVAVEL_FRAMEWORK . 'Query/Query.php';
require_once EVAVEL_FRAMEWORK . 'Query/ApplyFilter.php';

// Requests
require_once EVAVEL_FRAMEWORK . 'Http/Request/Traits/CountsResources.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/Traits/DecodesFilters.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/Traits/QueriesResources.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/Traits/ManageSettings.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/Interfaces/AuthorizeWhenResolved.php';

require_once EVAVEL_FRAMEWORK . 'Http/Request/FilterDecoder.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/Request.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/IndexRequest.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/DetailRequest.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/UpdateRequest.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/UpdateBulkRequest.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/CreateRequest.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/DestroyRequest.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/DestroyBulkRequest.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/AppSettingsRequest.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/SettingsRequest.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/UserSettingsRequest.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/ActionRequest.php';
require_once EVAVEL_FRAMEWORK . 'Http/Request/LensIndexRequest.php';

// Validation
require_once EVAVEL_FRAMEWORK . 'Http/Validation/Traits/ValidatorRules.php';
require_once EVAVEL_FRAMEWORK . 'Http/Validation/Traits/ValidatorMessages.php';
require_once EVAVEL_FRAMEWORK . 'Http/Validation/Validator.php';

// Middlewares
require_once EVAVEL_FRAMEWORK . 'Http/Middleware/SanitizeMiddleware.php';

// Resources
require_once EVAVEL_FRAMEWORK . 'Resources/Traits/ResolvesFields.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Traits/PerformsValidation.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Traits/FillsFields.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Traits/Searchable.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Traits/Metable.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Traits/ResolvesFilters.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Traits/ResolvesReverseRelation.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Traits/ResolvesActions.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Traits/ResolvesLenses.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Traits/Authorizable.php';

require_once EVAVEL_FRAMEWORK . 'Resources/Filters/Filter.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Filters/DateFilter.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Filters/DateRangeFilter.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Filters/BooleanFilter.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Filters/RangeFilter.php';

require_once EVAVEL_FRAMEWORK . 'Resources/Interfaces/RelatableField.php';

require_once EVAVEL_FRAMEWORK . 'Resources/Resource.php';
require_once EVAVEL_FRAMEWORK . 'Resources/LensResource.php';

require_once EVAVEL_FRAMEWORK . 'Policies/Policy.php';

require_once EVAVEL_FRAMEWORK . 'Resources/Actions/Action.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Actions/DestructiveAction.php';

// Collections
require_once EVAVEL_FRAMEWORK . 'Models/Collections/Enumerable.php';
require_once EVAVEL_FRAMEWORK . 'Models/Collections/Arr.php';
require_once EVAVEL_FRAMEWORK . 'Models/Collections/EnumeratesValues.php';
require_once EVAVEL_FRAMEWORK . 'Models/Collections/Collection.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Actions/ActionFields.php';

// Models Interfaces
require_once EVAVEL_FRAMEWORK . 'Models/Interfaces/Authorizable.php';

// Models Traits
require_once EVAVEL_FRAMEWORK . 'Models/Traits/InteractsPivotTable.php';
require_once EVAVEL_FRAMEWORK . 'Models/Traits/HasRelationships.php';
require_once EVAVEL_FRAMEWORK . 'Models/Traits/HasTimestamps.php';
require_once EVAVEL_FRAMEWORK . 'Models/Traits/HasEvents.php';
require_once EVAVEL_FRAMEWORK . 'Models/Traits/HasMeta.php';
require_once EVAVEL_FRAMEWORK . 'Models/Traits/Authorizable.php';
require_once EVAVEL_FRAMEWORK . 'Models/Traits/HasAttributes.php';
require_once EVAVEL_FRAMEWORK . 'Models/Traits/HidesAttributes.php';

require_once EVAVEL_FRAMEWORK . 'Models/Relations/Relation.php';
require_once EVAVEL_FRAMEWORK . 'Models/Relations/BelongsTo.php';
require_once EVAVEL_FRAMEWORK . 'Models/Relations/HasOne.php';
require_once EVAVEL_FRAMEWORK . 'Models/Relations/HasMany.php';
require_once EVAVEL_FRAMEWORK . 'Models/Relations/BelongsToMany.php';

// Models
require_once EVAVEL_FRAMEWORK . 'Models/Model.php';
require_once EVAVEL_FRAMEWORK . 'Models/User.php';
require_once EVAVEL_FRAMEWORK . 'Models/Setting.php';
require_once EVAVEL_FRAMEWORK . 'Models/SettingSimple.php';
require_once EVAVEL_FRAMEWORK . 'Models/SettingSimpleGrouped.php';
require_once EVAVEL_FRAMEWORK . 'Models/SettingListing.php';
require_once EVAVEL_FRAMEWORK . 'Models/SettingCustomized.php';

// Pivot
require_once EVAVEL_FRAMEWORK . 'Models/Database/Pivot.php';

// Fields
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Panel.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Field.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/FieldCollection.php';

require_once EVAVEL_FRAMEWORK . 'Resources/Fields/ID.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Image.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Text.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Password.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Textarea.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Number.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/BelongsTo.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/HasMany.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/BelongsToMany.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Boolean.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Checkboxes.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Color.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Select.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Date.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/DateTime.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Timezone.php';
require_once EVAVEL_FRAMEWORK . 'Resources/Fields/Trix.php';

// Settings
require_once EVAVEL_FRAMEWORK . 'Resources/settings/Panel.php';
require_once EVAVEL_FRAMEWORK . 'Resources/settings/Setting.php';
require_once EVAVEL_FRAMEWORK . 'Resources/settings/Text.php';
require_once EVAVEL_FRAMEWORK . 'Resources/settings/Select.php';
require_once EVAVEL_FRAMEWORK . 'Resources/settings/Boolean.php';

// Notifications
require_once EVAVEL_FRAMEWORK . 'Notifications/Notifications.php';
require_once EVAVEL_FRAMEWORK . 'Notifications/Notification.php';

// Log files
require_once EVAVEL_FRAMEWORK . 'Log/Log.php';
require_once EVAVEL_FRAMEWORK . 'Http/Controllers/LogController.php';
