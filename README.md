# CiviContact API (au.com.agileware.civicontactapi)

This is a [CiviCRM](https://civicrm.org) extension required for the [CiviContact application](https://civicontact.com.au) for iOS and Android. Find out more about about CiviContact at [civicontact.com.au](https://civicontact.com.au)

This extension **must** be installed in CiviCRM for the CiviContact app to function.

## CiviContact 

As of **15th January 2020**, the CiviContact app is only available to **registered testers** in the iTunes and Google Play Store.
If you would  like to be a CiviContact app tester then please email [civicontact@agileware.com.au](mailto:civicontact@agileware.com.au) and tell us:
1. Which device you are using either: [Android](https://www.android.com/) or [iPhone](https://apple.com/iphone)
2. The email address you use to login to [iTunes](https://apple.com/itunes) or the [Google Play Store](https://play.google.com/)

When you become a tester we will email you and ask for your feedback on the app. We are interested in hearing about both improvements and any bugs you may find.

We expect do a public release of the CiviContact app in **February 2020**. 

## Installation
1. Download the [CiviContactAPI extension](https://github.com/agileware/au.com.agileware.civicontactapi/archive/master.zip).
2. Install the CiviContact extension to the CiviCRM extensions directory to a new **civicontact** sub-directory
3. Enable the CiviContact extension in CiviCRM on the Extensions page

### CiviCRM Base URL needs to be set

CiviContact needs to know the URL of the CiviCRM site. So make sure that the base URL of the CiviCRM site is set correctly in the civicrm.settings.php. If this is not set or incorrect, then CiviContact authentication will fail.

### Patch required for CiviCRM on WordPress 

For WordPress websites with CiviCRM installed, please download and apply this patch to CiviCRM: [rest-wp.patch](rest-wp.patch)

As of **22nd December 2019**, there are proposed changes to improve CiviCRM REST API support for WordPress. These changes have not been tested with CiviContact. Use the included [rest-wp.patch](rest-wp.patch) for now. References 
[PR #160 - Merge REST API wrapper code](https://github.com/civicrm/civicrm-wordpress/pull/160) and [WordPress Development roadmap
](https://lab.civicrm.org/dev/wordpress/issues/20#civicrm-rest-api)
 
## Authenticating CiviContact with CiviCRM

There are two methods available for authenticating CiviContact with CiviCRM. _Note: Both methods require that the authenticating user have a valid user account in the website which is linked to their CiviCRM contact record._

### Authentication using the QR Code method

Use this method if your users are familiar with logging into CiviCRM and accessing their own contact record.

1. Login to CiviCRM.
2. Locate the contact record in CiviCRM linked to your user account. _Note: You cannot view the QR code for another contact record._
3. Open the tab is added with name **CiviContact Authentication**. You should see a QR Code image on this page.
4. Launch the CiviContact app and click on **Scan QR Code** on initial screen.
5. If prompted, grant permission for CiviContact to access your camera.
6. Scan the QR Code.
7. CiviContact should then complete the authentication and immediately sync with CiviCRM.

### Authentication using the CiviContact authentication URL method

Use this method if you have many users to set up with CiviContact and/or if users are not logging into CiviCRM frequently. Send an email with a special CiviContact authentication URL (token: **{civicontact.authUrl}**), when clicked on a mobile device this will launch CiviContact and authenticate with CiviCRM.
* The authentication URL **will only work on the mobile device** and will not work if clicked on from desktop email client.
* The CiviContact authentication link will **expire after 24 hours** and a new link will be required. 

#### Authenticating individual CiviContact users

When viewing a CiviCRM contact, in the **Actions menu** there is a new action available **Send CiviContact authentication**.
When this action is executed an email will be sent immediately to the contact primary email address with the CiviContact authentication URL.
You can override the contents of this email located in **templates/CRM/Civicontact/email.tpl**

#### Authenticating many CiviContact users

A special CiviCRM token is available **{civicontact.authUrl}** which can be used in **Send Email** action, **Bulk Mailings** (newsletters) and **Scheduled Reminders**.
Include the **{civicontact.authUrl}** in the email to generate a unique CiviContact authentication URL for each recipient of the email.

## Configuration

To configure CiviContact in CiviCRM, navigate to **Administer > CiviContact > Settings**.

On this page you can configure:
* **Enable Global Config**: disabled by default. If enabled, this will prevent CiviContact settings being changed locally and instead apply a global configuration to all users.
* **Email to Activity**: if enabled this option will automatically record activity when the user emails any contact from CiviContact.
* **Favourite Tile Click Action**: the default action to be executed when a Favourite Contact tile is clicked, actions can be either: Create Email or Create Phone Call. CiviContact will skip the phone or email action if there is no phone or email available for the Contact.
* **Contact Activity types**: the activity types which are listed in the "Activities" for a Contact.
* **Contact Profile**: select the CiviCRM Profile to be used for displaying the fields on the add, view end edit pages in CiviContact. By default this is set to the CiviContact Profile.
* **Sync Interval**: determines how often CiviContact should sync with the CiviCRM site.
* **Reset QR Code**: re-generate QR Codes for all users.
* **Drop Authentication**: immediately invalidate all existing user CiviContact authentication, requiring all users to re-authenticate. 

### Syncing groups with CiviContact

To enable a CiviCRM Group (either Standard Group or Smart Group) to be synced with CiviContact:
1. Open Groups page, **Contacts > Manage Groups**
2. Click **Settings** of the Group to sync with CiviContact.
3. Enable the **Sync to CiviContact** option.
4. Click **Save** to apply the change.

When CiviContact syncs again with CiviCRM, this Group and the Contacts in the Group will be synced.
Any changes to the Group in CiviCRM will be reflected in CiviContact automatically.

### Default group for new contacts

When the CiviContact API extension is installed, a new CiviCRM Group **CiviContact** is added. When new Contacts are added in CiviContact, each Contact will be automatically added to this Group in CiviCRM.

### Default profile for CiviContact

When the CiviContact API extension is installed, a new CiviCRM profile **CiviContact** is added.
This profile is intended to be used to add new fields to CiviContact, rename existing fields, re-order fields and remove fields, enabling you to fully customise the CiviContact user interface without any coding required.

## Customising CiviContact

Add your custom CiviCRM fields to the CiviContact profile to enable your users to view and update information specific to your requirements.

**[Summary Fields](https://civicrm.org/extensions/summary-fields)** make it easier to search for major donors, recent donors, lapsed donors as well as to show a synopsis of a donor’s history. CiviContact supports the following CiviCRM extensions:
* [Summary Fields](https://civicrm.org/extensions/summary-fields)
* [Joinerys More Summary Fields](https://civicrm.org/extensions/joinerys-more-summary-fields)

**[CiviTeams integration](https://github.com/agileware/au.com.agileware.civiteams)** enables you to easily segment your users into virtual teams and precisely control which Contacts and Groups are available in CiviContact.

For example, setting up two virtual CiviTeams, CiviTeam A can access Group 1 and Group 2 using CiviContact, CiviTeam B can access Group 3 and Group 4.


About the Authors
-----------------

This CiviCRM extension was developed by the team at
[Agileware](https://agileware.com.au).

[Agileware](https://agileware.com.au) provide a range of CiviCRM services
including:

  * CiviCRM migration
  * CiviCRM integration
  * CiviCRM extension development
  * CiviCRM support
  * CiviCRM hosting
  * CiviCRM remote training services

Support your Australian [CiviCRM](https://civicrm.org) developers, [contact
Agileware](https://agileware.com.au/contact) today!


![Agileware](logo/agileware-logo.png)
