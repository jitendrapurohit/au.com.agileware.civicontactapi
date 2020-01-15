# CiviContact API (au.com.agileware.civicontactapi)

## CiviContact is available for limited testing only

As of **15th January 2020**, the CiviContact app is only available to **registered testers** in the iTunes and Google Play Store.
If you would  like to be a CiviContact app tester then please email [civicontact@agileware.com.au](mailto:civicontact@agileware.com.au) and tell us:
1. Which device you are using either: Android or iPhone
2. The email address you use to login to iTunes or the Google Play Store

When you become a tester we will email you and ask for your feedback on the app. We are interested in hearing about both improvements and any bugs you may find.

We expect do a public release of the CiviContact app in **February 2020**. 

## About
This is a [CiviCRM](https://civicrm.org) extension to support the CiviContact mobile application. This extension **must** be installed on the CiviCRM site for the CiviContact mobile application to function.

For WordPress websites with CiviCRM installed, please download and apply this patch to CiviCRM: [rest-wp.patch](rest-wp.patch)

_Note_: As of 22/12/2019, there are proposed changes to improve CiviCRM REST API support for WordPress. These changes have not been tested with CiviContact. Use the included [rest-wp.patch](rest-wp.patch) for now. References 
[PR #160 - Merge REST API wrapper code](https://github.com/civicrm/civicrm-wordpress/pull/160) and [WordPress Development roadmap
](https://lab.civicrm.org/dev/wordpress/issues/20#civicrm-rest-api)

## Usage
Just install the extension and it is ready to be used with CiviContact mobile application.  

CiviContact extension exposes contacts of selected groups only. To allow any group to be synced with mobile application follow the steps.
1. Open Groups page, Contacts > Manage Groups
2. Click **Settings** of any group which you want to get synced with CiviContact mobile application.
3. There is an option **Sync to CiviContact**, Check it and click save.

Now contacts from the above group will be synced with the CiviContact mobile application. If contact is added/removed from the Group, it will be updated in mobile application on next sync.

By default extension adds a Group named **CiviContact**, any contact which is added from the mobile device will be added in this group by default.

## Login from mobile

To login into the mobile application follow the steps.

1. Click on **Scan QR Code** on Welcome screen, That will ask you for permission to access camera if you have not already given the access.
2. Clicking allow will open the camera with QR code scanner.
3. Login into CiviCRM
4. Open the CiviCRM Contact which is linked to your website login (your CiviCRM Contact).
5. View the Contact, open the tab is added with name **CiviContact Authentication**.
6. Click on this tab and a QR code should now be displayed.
7. Scan this QR code using the CiviContact app.

And that's all! CiviContact mobile app will fetch all the contacts from the **Sync to CiviContact** groups.

## Configuration

Open Administer > CiviContact > Settings to open extension's configuration page. Following options are avilable to configure

1. **Enable Global Config**, it is disabled by default. If it is set as "yes" application config will be same for all users and it can't be changed from App settings.
2. **Email to Activity**, This is global configuration, if the above setting is turned on we will consider this config for all the users. This config is used to record an activity when a user emails any contact.
3. **Sync Interval**, This is again global configuration, if the first setting is turned on we will consider this config for all the users. This config is used to set sync interval at which sync should get executed in mobile application.

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
