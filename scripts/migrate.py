mfdir = '../../extensions/MobileFrontend'

import json, shutil, os, subprocess, time
from collections import OrderedDict
import sys

DRY_RUN = False
f = open(mfdir +'/extension.json', 'r')
mfExtensionData = json.load(f, object_pairs_hook=OrderedDict)
f.close()
f = open('skin.json', 'r')
minervaSkinData = json.load(f, object_pairs_hook=OrderedDict)
f.close()

messages = [ 'mobile-frontend-placeholder', 'skinname-minerva',
  'mobile-frontend-talk-back-to-userpage',
  'mobile-frontend-talk-back-to-projectpage',
  'mobile-frontend-talk-back-to-filepage',
  'mobile-frontend-talk-back-to-page',
  'mobile-frontend-editor-edit',
  'mobile-frontend-user-newmessages',
  'mobile-frontend-main-menu-contributions',
  'mobile-frontend-main-menu-watchlist',
  'mobile-frontend-main-menu-settings',
  'mobile-frontend-home-button',
  'mobile-frontend-random-button',
  'mobile-frontend-main-menu-nearby',
  'mobile-frontend-main-menu-logout',
  'mobile-frontend-main-menu-login',
  'mobile-frontend-history',
  'mobile-frontend-user-page-member-since',
  'mobile-frontend-main-menu-button-tooltip',
  'mobile-frontend-language-article-heading',
  'mobile-frontend-pageaction-edit-tooltip',
  'mobile-frontend-language-article-heading',
  'mobile-frontend-user-page-talk',
  'mobile-frontend-user-page-contributions',
  'mobile-frontend-user-page-uploads'
]

def reset():
    # Do cleanup in preparation for patchsets it will make.
    subprocess.call(["git clean -fd"], shell=True)
    subprocess.call(["git stash && git clean -fd"], shell=True, cwd=mfdir)
    if not DRY_RUN:
        subprocess.call(["rm -rf includes && rm -rf resources && rm -rf minerva.less && rm -rf i18n && rm -rf skinStyles && rm -rf tests/qunit && rm -rf tests/phpunit"], shell=True)
        subprocess.call(["mkdir includes && mkdir includes/skins && mkdir includes/models && mkdir resources && mkdir minerva.less && mkdir i18n && mkdir skinStyles && mkdir tests/qunit && mkdir tests/qunit/skins.minerva.notifications.badge && mkdir tests/phpunit/ && mkdir tests/phpunit/skins"], shell=True)
        time.sleep(1)

def saveJSON(path, data, sort_keys = False):
    if not DRY_RUN:
        with open(path, 'w') as outfile:
            json.dump(data, outfile, indent = "\t", ensure_ascii=False, separators=(',', ': '), sort_keys=sort_keys)

def clean( keys_to_remove, data_key ):
    # Remove the keys we added from MobileFrontend extension.json
    for key in keys_to_remove:
        try:
            del mfExtensionData[data_key][key]
        except KeyError:
            pass

def steal_files_in_directory(dir):
    if not DRY_RUN:
        # steal templates etc from MobileFrontend
        for root, dirs, files in os.walk(mfdir + '/' + dir):
            for file in files:
                steal(root + '/' + file)

def steal( path ):
    dest = path.replace(mfdir + '/', '' )
    origin = mfdir + '/' + path
    print('\tstealing %s from %s'%(dest, origin))
    try:
        if not DRY_RUN:
            shutil.move( origin, dest )
    except ( FileNotFoundError, shutil.Error ) as e:
        print (e)
        # probably done in initial steal steps
        pass

def copy( path ):
    if not DRY_RUN:
        print('copying %s'%path)
        try:
            # ../ because we are in scripts folder
            shutil.copy( mfdir + '/' + path, path )
        except ( FileNotFoundError, shutil.Error ) as e:
            print (e)
            # probably done in initial steal steps
            pass

def isOwnedByMinerva(key, value=""):
    return key.startswith("Minerva") or key.endswith("Minerva") or key.startswith( 'skins.minerva' ) or \
        ( type(value) == type('string') and "skins/" in value )

# modulename, ResourceModules
def migrateResourceModuleSkinStyles():
    mf = {}
    minerva = {}

    for module, files in mfExtensionData["ResourceModuleSkinStyles"]["minerva"].items():
        for f in files:
            try:
                os.makedirs('skinStyles/%s'%module)
            except FileExistsError:
                pass
            steal(f)
        minerva[module] = files

    minervaSkinData["ResourceModuleSkinStyles"]["minerva"] = minerva
    del mfExtensionData["ResourceModuleSkinStyles"]["minerva"]

def migrateObject(fromObj, toObj, key):
    obj = fromObj[key]
    keys_to_remove = []
    files_to_steal = []
    for subkey in obj:
        val = obj[subkey]
        if isOwnedByMinerva(subkey, val):
            if key == "ResourceModules":
                steal( 'resources/%s'% subkey )
                if "messages" in obj[subkey]:
                    for msg in obj[subkey]["messages"]:
                        if type(obj[subkey]["messages"]) == type([]):
                            messages.append(msg)
                        else:
                            msgVal = obj[subkey]["messages"][msg]
                            if type(msgVal) == type([]):
                                messages.append(msg)
                            else:
                                messages.append(msgVal)
            elif key == 'AutoloadClasses':
                steal( val )

            toObj[key][subkey] = val
            keys_to_remove.append(subkey)

    # cleanup
    clean( keys_to_remove, key )
    print('Moved keys from %s:\n\t\t%s'%(key, keys_to_remove))

def stealHooks():
    for hookname in mfExtensionData['Hooks']:
        mfHooks = mfExtensionData['Hooks'][hookname]
        minervaHooks = []
        newMfHooks = []
        for hook in mfHooks:
            if isOwnedByMinerva(hook, hook):
                minervaHooks.extend([hook])
            else:
                newMfHooks.extend([hook])
        if len(minervaHooks) > 0:
            minervaSkinData['Hooks'][hookname] = minervaHooks
        mfExtensionData['Hooks'][hookname] = newMfHooks

################################################################################################################################
######## SCRIPT BEGINS HERE
################################################################################################################################
reset()

minervaSkinData['ResourceModuleSkinStyles']['minerva'] = {}
migrateObject( mfExtensionData, minervaSkinData, "AutoloadClasses")
migrateObject( mfExtensionData, minervaSkinData, "ResourceModules")
migrateObject( mfExtensionData, minervaSkinData, "config")
migrateResourceModuleSkinStyles()
stealHooks()


steal_files_in_directory('includes/skins/')
steal_files_in_directory('minerva.less/')
steal_files_in_directory('tests/qunit/skins.minerva.notifications.badge')
steal_files_in_directory('tests/phpunit/skins')

# steal the service wirings
steal('includes/Minerva.ServiceWiring.php');

# remove
del mfExtensionData["ValidSkinNames"]
# for time being we do this. When code has been removed from MobileFrontend can switch to minerva-neue
minervaSkinData["ValidSkinNames"] = {
    "minerva-neue": "MinervaNeue"
}
try:
    del minervaSkinData["ServiceWiringFiles"]
except KeyError:
    pass

# add onRegistration hook
minervaSkinData['callback'] = 'MinervaHooks::onRegistration';

# cleanup
minervaSkinData['namemsg'] = 'skinname-minerva'
minervaSkinData['descriptionmsg'] = "minerva-skin-desc"
minervaSkinData['ConfigRegistry']['minerva'] = 'GlobalVarConfig::newInstance'
del minervaSkinData['ResourceModuleSkinStyles']['minerva-neue']
del minervaSkinData['ConfigRegistry']['minerva-neue']

# setup registration
minervaSkinData["callback"] = "MinervaHooks::onRegistration";

saveJSON('skin.json', minervaSkinData)
saveJSON(mfdir + '/extension.json', mfExtensionData)

# migrate i18n
print('migrating i18n')
for root, dirs, files in os.walk(mfdir + '/i18n/'):
    for language in files:
        f = open(mfdir + '/i18n/' + language, 'r')
        try:
            newLanguageData = json.load(f, object_pairs_hook=OrderedDict)
        except IOError:
            newLanguageData = {}
        f.close()

        f = open(mfdir + '/i18n/' + language, 'r')
        languageData = json.load(f, object_pairs_hook=OrderedDict)
        f.close()
        messages.sort()
        if language == 'en.json':
            print("Moving %s messages"%len(messages))
        for msgKey in messages:
            if language == 'en.json':
                print("\t\tMap message %s"%(msgKey))
            try:
                newLanguageData[msgKey] = languageData[msgKey]
                del languageData[msgKey]
            except KeyError:
                pass

        # save to mf
        saveJSON( mfdir + '/i18n/' + language, languageData )

        # save to minerva
        saveJSON( 'i18n/' + language, newLanguageData, True )
