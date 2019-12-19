# CiviContact API (au.com.agileware.civicontactapi)
## About
This is a [CiviCRM](https://civicrm.org) extension to support the CiviContact mobile application. This extension **must** be installed on the CiviCRM site for the CiviContact mobile application to function.

For WordPress websites with CiviCRM installed, please download and apply this patch to CiviCRM: [rest-wp.patch](rest-wp.patch)

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
4. Open your profile page, a new last tab is added with name **CiviContact Authentication** by CiviContact extension. Click on it.
5. Now scan this QR code from mobile device.

And that's all! CiviContact mobile app will fetch all the contacts from the **Sync to CiviContact** groups.

## Configuration

Open Administer > CiviContact > Settings to open extension's configuration page. Following options are avilable to configure

1. **Enable Global Config**, it is disabled by default. If it is set as "yes" application config will be same for all users and it can't be changed from App settings.
2. **Email to Activity**, This is global configuration, if the above setting is turned on we will consider this config for all the users. This config is used to record an activity when a user emails any contact.
3. **Sync Interval**, This is again global configuration, if the first setting is turned on we will consider this config for all the users. This config is used to set sync interval at which sync should get executed in mobile application.
