# Partner and Spouse Email Greeting (au.com.agileware.partneremailgreeting)

This is a [CiviCRM](https://civicrm.org) extension which automatically changes a
Contact's Email Greeting to include the First Name of a related Contact when
that relationship is of type **Partner Of** or **Spouse Of**. For example,
"Dear Fran" becomes "Dear Fran and Justin".

This is useful for households that share a single email address, so that
emails sent to that address greet both people rather than just one.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## How it works

* When a **Partner Of** or **Spouse Of** relationship (between two
  Individual contacts) is **created**, or an existing one is **edited and
  enabled**, both contacts' Email Greeting is set to *Customized* with the
  text "Dear `<First Name A>` and `<First Name B>`".
* When such a relationship is **disabled**, the Email Greeting for both
  contacts is reverted to the standard "Dear `{contact.first_name}`"
  greeting.
* If either related contact does not have a **First Name** set, the
  standard greeting is used instead of the combined greeting.
* A contact is excluded from the combined greeting (and reverted to the
  standard greeting) if they are deceased, deleted, or have Do Not Email,
  Do Not Trade, or Opt Out set.

## Usage

This extension works automatically in the background; there is no
end-user interface. It provides:

* A hook on Relationship `create`/`edit` events, so the Email Greeting is
  updated immediately when a qualifying relationship is added, edited, or
  disabled.
* A Scheduled Job, **Update Partner Email Greeting**
  (`Job.Partneremailgreeting`), which re-processes every active *Partner
  Of* and *Spouse Of* relationship and (re)applies the correct Email
  Greeting to both contacts. This is useful as a periodic catch-all, for
  example if a related contact's First Name is added or changed after the
  relationship was created (a change which is not itself detected by the
  relationship hook).

## Special configuration requirements

There are no settings pages, credentials, or dependent extensions to
configure. The only setup step is enabling the **Update Partner Email
Greeting** Scheduled Job (see Installation below) so that the periodic
catch-all run occurs; by default it runs daily.

## Requirements

* CiviCRM 5.51+

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin
Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

1. Install and enable this CiviCRM extension like any normal CiviCRM extension.
1. Enable the Scheduled Job, `Update Partner Email Greeting`.

# About the Authors

This CiviCRM extension was developed by the team at [Agileware](https://agileware.com.au).

[Agileware](https://agileware.com.au) provide a range of CiviCRM services including:

* CiviCRM migration
* CiviCRM integration
* CiviCRM extension development
* CiviCRM support
* CiviCRM hosting
* CiviCRM remote training services

Support your Australian [CiviCRM](https://civicrm.org) developers, [contact Agileware](https://agileware.com.au/contact) today!

![Agileware](images/agileware-logo.png)
