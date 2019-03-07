MinervaNeue skin
========================

The MinervaNeue skin is a responsive mobile-first skin for your mediawiki instance.

Installation
------------

See <https://www.mediawiki.org/wiki/Skin:MinervaNeue>

Development
-----------

### Coding conventions

Please follow the coding conventions of MobileFrontend:
<https://www.mediawiki.org/wiki/MobileFrontend/Coding_conventions>

### Config

The following configuration options will apply only to the default mobile skin - Minerva.

#### $wgMinervaAlwaysShowLanguageButton

* Type: `Boolean`
* Default: `true`

Whether to show the language switcher button even if no languages are available
for the page.

#### $wgMinervaEnableSiteNotice

* Type: `Boolean`
* Default: `false`

Controls whether site notices should be shown.
See <https://www.mediawiki.org/wiki/Manual:$wgSiteNotice>.

#### $wgMinervaCountErrors
* Type: `Boolean`
* Default: `false`

Whether to count client side errors in statsv.

#### $wgMinervaErrorLogSamplingRate
* Type: `Integer`
* Default: `0`

Whether to log client side errors to EventLogging. If 0, error logging is disabled.
If 0.5, 50% of all client side errors will be logged to the EventLogging client.
If 1, all errors will be logged to the EventLogging client, thus when enabling this
care should be taken that your setup is bug free in order to not overwhelm the EventLogging
server.

#### $wgMinervaShowCategoriesButton

* Type: `Array`
* Default:
```php
  [
    'base' => false,
    'beta' => true,
  ]
```
Controls whether the category button should be displayed.

#### $wgMinervaApplyKnownTemplateHacks

* Type: `Boolean`
* Default: `false`

When enabled and hacks.less exists, hacks.less workarounds are included in stylesheet. These should only be needed for Wikimedia based wikis or wikis using common templates such as Template:Infobox on those wikis.

#### $wgMinervaPageActions

* Type: `Array`
* Default: `['edit', 'talk', 'watch', 'switch-language']`

Controls which page actions, if any, are displayed. Allowed: `edit`, `watch`, `talk`, and
`switch-language`.


#### $wgMinervaPageIssuesNewTreatment

* Type: `Array`
* Default:
```php
  [
    'base' => false,
    'beta' => true,
  ]
```
Controls whether page issues should be replaced with a "Page issues" link (false) or displayed inline (true).

#### $wgMinervaTalkAtTop

* Type: `Array`
* Default:
```php
  [
    'beta' => false,
    'base' => false,
    'amc' => true,
  ]
```
Controls whether the talk option should be displayed at the top of the page.
This will work for all pages except the main page.

#### $wgMinervaHistoryInPageActions

* Type: `Array`
* Default:
```php
  [
    'beta' => false,
    'base' => false,
    'amc' => true,
  ]
```
Controls whether the history link appears in the page actions menu.

#### $wgMinervaShowShareButton

* Type: `Array`
* Default:
```php
  [
    'beta' => false,
    'base' => false,
  ]
```
Controls whether the share feature should be added to the page actions menu.

#### $wgMinervaEnableBackToTop

* Type: `Array`
* Default:
```php
  [
    'base' => false,
    'beta' => true,
  ]
```
Controls whether the a back to top button should appear in the bottom right of the screen when scrolling.

#### $wgMinervaCustomLogos

* Type: `Array`
* Default: `[]`

Make the logos configurable.

Currently, `copyright`, `copyright-fallback`, `copyright-width`, and `copyright-height` elements are
supported.

* `copyright` is the URL of the logo displayed in the header and footer
* `copyright-fallback` is the URL of the fallback logo displayed on
  non-supported browsers like IE8 or Opera Mini
* `copyright-width` (optional) is the width in pixels of the copyright image
  you want to display
* `copyright-height` (optional) is the height in pixels of the copyright image
  you want to display
* If the actual `copyright` dimensions are 200x30, then you may want to set the
  width and height to 100 and 15 respectively (in order to support retina
  screens).
* Note that if -width and -height are not used sysadmin should ensure the image
used is appropriately sized (suggested dimensions < 120px width and 18px height).

Example:
```php
[
  'copyright' => '/images/mysite_copyright_logo.png',
  'copyright-width' => 100,
  'copyright-height' => 15,
]
```

Example with fallback URL:
```php
[
  'copyright' => '/images/mysite_copyright_logo.svg',
  'copyright-fallback' => '/images/mysite_copyright_logo.svg.png',
  'copyright-width' => 100,
  'copyright-height' => 15,
]
```

#### $wgMinervaAlwaysShowLanguageButton

* Type: `Boolean`
* Default: `true`

Whether to show the language switcher button even if no languages are available for the page.

#### $wgMinervaABSamplingRate

* Type: `Number`
* Default: `0`

On a scale of 0 to 1, determines the chance a user has of entering an AB test.
A test is divided into 3 buckets, "control" "A" and "B". Users that are selected for the
test have an equal chance of entering bucket "A" or "B", the remaining users fall into the
"control" bucket and are excluded from the test.

1    - would run test on 100% of users (50% in A and 50% in B, 0 in control).
0.5  - would run test on 50% of users (25% in A, 25% in B, 50% in control).
0.05 - would run test on 5% of users (2.5% in A, 2.5% in B, 95% in control).
0 would disable the test and place all users in "control".

Group assignment is universal no matter how many tests are running since both
`wgMinervaABSamplingRate` and `mw.user.sessionId()` are globals.

Group membership can be debugged from the console via:

```js
  const AB = mw.mobileFrontend.require('skins.minerva.scripts/AB')
  new AB({
    testName: 'WME.PageIssuesAB',
    samplingRate: mw.config.get( 'wgMinervaABSamplingRate', 0 ),
    sessionId: mw.user.sessionId()
  }).getBucket()
```

And since session ID is an input in calculating the group, reassignment occurs
when clearing it: `mw.storage.session.remove('mwuser-sessionId')`.

#### $wgMinervaSchemaMainMenuClickTrackingSampleRate

Defines the sampling rate for the MobileWebMainMenuClickTracking schema.

* Type: `Number`
* Default: `0.5`
