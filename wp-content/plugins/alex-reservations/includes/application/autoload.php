<?php


require_once ALEXR_PLUGIN_DIR_APP . 'database/db-restaurants.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-restaurant-meta.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-restaurant-setting.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-bookings.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-booking-meta.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-booking-notifications.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-booking-reviews.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-booking-table.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-btaggroups.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-btags.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-booking-btag.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-customers.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-customer-meta.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-users.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-user-meta.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-restaurant-user.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-settings.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-floors.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-notifications.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-payments.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-areas.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-tables.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-combinations.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-combination-table.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-customers.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-ctaggroups.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-ctags.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-customer-ctag.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-roles.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-actions.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-daily-notifications.php';
require_once ALEXR_PLUGIN_DIR_APP . 'database/db-tokens.php';

require_once ALEXR_PLUGIN_DIR_APP . 'database/factories/factory.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Config/AppConfigurator.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Enums/BookingStatus.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Enums/BookingType.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Enums/UserRole.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Enums/ReviewItems.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Enums/Countries.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Enums/CurrencyType.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Enums/PaymentStatus.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Enums/DateTranslations.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/CalculateBookingFormDates.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/CalculateBlockedTables.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/CalculateBlockedSlots.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/CalculateBookingMetrics.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/CsvHelpers.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/SendBookingEmails.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/SendBookingSms.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/SendBookingEmailReminders.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/SendBookingSmsReminders.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/StoreEmailSent.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/StoreSmsSent.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/ReturnBookingMessages.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/ManagePermissions.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/ManageAddToCalendar.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/BookingUsePayments.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/HasSettings.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Traits/CustomerPaymentUtils.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Traits/HasTimeOptions.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Traits/FieldsShiftEvent.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Traits/ShiftCalculations.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Traits/TablesCalculations.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Traits/LoadEmailTemplates.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Traits/ManagePayments.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Restaurant.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Booking.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/BookingNotification.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/BookingReview.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/BTagGroup.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/BTag.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/User.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/UserMeta.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Customer.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/CTag.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/CTagGroup.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Floor.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Area.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Table.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Combination.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Role.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Notification.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Payment.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/DailyNotification.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Token.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Models/Action.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Notifications/BookingOnlineReceived.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Notifications/BookingCancelledByUser.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Notifications/BookingConfirmedByUser.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Notifications/BookingFeedbackReceived.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/General.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Dashboard.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/GoogleReserve.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Panorama.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Profile.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Scheduler.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Shift.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Event.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/ClosedDay.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/ClosedSlot.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/ClosedTable.php';
//require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/BlockedHour.php'; // no lo uso
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Waitlist.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/EmailConfig.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/EmailCustom.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/EmailTemplate.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/SmsTemplate.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/CustomerTag.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/BookingTag.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/WidgetForm.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/WidgetMessage.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/EmailReminder.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/SmsReminder.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Payment.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/SocialChannel.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Interfaces/FilterSlots.php';
//require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Filters/FilterByBlockSlots.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Filters/FilterByDuration.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Filters/FilterByNearestSlots.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Filters/FilterByMinMaxGuests.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Settings/Filters/FilterByClosedSlots.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Policies/Traits/InteractsWithTenants.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Policies/UserPolicy.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Policies/BookingPolicy.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Policies/RestaurantPolicy.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/Restaurant.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/Booking.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/User.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/Customer.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/Floor.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/Area.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/Table.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/Combination.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/Lenses/BookingsToday.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/Btag.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/Btaggroup.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/Ctag.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Resources/Ctaggroup.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Filters/BookingStatusFilter.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Filters/BookingFutureFilter.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Filters/BookingDateFilter.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Filters/BookingDateRangeFilter.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Actions/ChangeDate.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Actions/ChangeStatus.php';

// Traits for controllers
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Traits/SendEmailsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Traits/SendSmsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Traits/DownloadCsvController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Traits/ShiftMetricsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Traits/BookingsUsePaymentsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Traits/GeneralMetricsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Traits/UISettingsController.php';

// Controllers
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/FloorPlanController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/ShiftsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/BTagsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/BookingsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/CTagsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/CustomersController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/PaymentsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/RestaurantsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/ReviewsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/UsersController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/WpUsersController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/RolesController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/SearchBookingsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/SendTestEmailController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/SendTestSmsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/NotificationsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/ProfileController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/SupportController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/LicenseController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/LogoutController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/WizardController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/TranslateController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/DashboardController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/UploadFileController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/DailyNotificationsController.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/Controllers/ClosedTablesController.php';

// Mobile Controllers
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Http/MobileControllers/MobileController.php';

// Events
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Events/EventBookingCreated.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Events/EventBookingModified.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Events/EventBookingStatusChanged.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Events/EventBookingTablesChanged.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Events/EventBookingSeatsChanged.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Events/EventBookingStatusChangedByCustomer.php';

// Listener
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Listeners/BookingStatusListener.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Listeners/BookingActionLogListener.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Listeners/ListenBookingEvents.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Observers/BookingObserver.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Observers/TokenObserver.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Observers/TableObserver.php';

require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Providers/AppServiceProvider.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Providers/EventServiceProvider.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Providers/AuthServiceProvider.php';

// Helpers
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/helpers/helpers.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/helpers/helpers-settings.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/helpers/helpers-services.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/helpers/helpers-sms.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/helpers/helpers-demo.php';
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/helpers/helpers-meta.php';

// HOOKS
// Add permissions to manage resources
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Hooks/HooksResourceController.php';
// Add permissions to manage app settings
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Hooks/HooksAppSettings.php';

// Mail
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Mail/MailManager.php';

// Sms
require_once ALEXR_PLUGIN_DIR_APP . 'Alexr/Sms/SmsManager.php';

