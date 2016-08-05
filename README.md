# Pivots extraction system
This simple tool is made for extract the content from the website into pivots format (JSON files) to import them with an Integration module.
Is built on top of open source PHP libraries:
* **guzzlehttp/guzzle:** used to download the content from the website.
* **scotteh/php-dom-wrapper:** used to query the DOM with CSS3 selectors.
* **symfony/console**: used to wrap custom code and launch it as command line instructions. 

## Requirements
* PHP 5.6
* Composer PHP dependencies manager

## Installation
Run the next command to install php libraries with composer:
* composer install

## How to use
* Currently there are implemented 2 commands in order to extract content:
    * app/console.php app:extract-menu --page=index_en.htm --entity=service_tools
        * This command will extract the service tools menu in all languages from the index_en.htm page.
    * app/console.php app:extract-content --page=sitemap --entity=common_page
        * This command will extract the common pages in all languages from the sitemap page.
    * app/console.php list
        * Is used to list all available commands.
    * app/console *command* --help to get the output of the help for the command.

## Code Structure
* **Antonov/src/Config.php**
    * Used to parse the configuration JSON files under Antonov/config
    * To instantiate it a static method Config::getInstance() is should be used.
    * The configuration is should be returned using the public getConfig() method.migration.ec.digitalchannels.technology/clima
* **Antonov/src/Caller.php**
    * Used to call the URL with Guzzle library and download the content.
    * It should have the URL and domain name to be instantiated.
    * It also could change the URL and domain name at runtime.
    * The method to perform the request is callResource(), if the response header is distinct to 200 it will return FALSE.
* **Antonov/src/Document.php**
    * Used to perform the CSS3 selection and markup manipulation with PHPDomWrapper library.
    * It uses the configuration of the fields defined on entity types to extract the fields and menus, configuration of the sitemap links defined on config to get the list of the links and get the languages versions from a dropdown selector. 
    * The methods for perform manipulation starts with "perform" prefix, more can be added for additional processing.
    * The method applyFieldSettings perform operations over the field content defined on entity type configuration file.
* **Antonov/src/Pivot.php**
    * Used to create the pivot structure and save it under Antonov/fixtures folder.
    * This class reads the entity_type configuration file in order to create the pivot structure.
* **Antonov/src/ProcessHandler.php**
    * This class is used to launch the content extraction, it make use of the Caller, Document and Pivot classes to get, manipulate and save the pivot classes.
* **Antonov/src/Command/ExtractAbstractCommand.php**
    * It defines the base arguments for the command line and instantiate the ProcessHandler object.
* **Antonov/src/Command/ExtractContentCommand.php**
    * This class is used to define the command to launch with **app/console.php app:extract-content page entity** in order to extract the content.
    * It launches the ProcessHandler's launch method.
* **Antonov/src/Command/ExtractMenuCommand.php**
    * This class is used to define the command to launch with **app/console.php app:extract-menu page entity** in order to extract the content.
    * It launches the ProcessHandler's launchMenu method.
* **app/console.php**:
    * Command line tool based on Symfony/Console component which is used to launch extraction commands.
* **Antonov/config/config.json**: 
    * Main configuration file with next parameters:
        * *url_domain*: http://domain.com
        * *url_folder*: /subfolder
        * *language_selector*: CSS3 selector to language dropdown selector links
        * *sitemap_selector*: CSS3 selector to sitemap links, is used to get all base urls and download them
        * *menu_entities*: menu entities which should be extracted and should have config files under entity_types folder
        * *multi_page_entities*: content types which should be extracted and should have config files under entity_types folder
* **Antonov/config/entity_types/content_type.json**: 
    * Content type entity configuration file with next parameters for each field to extract:        
        * *name*: field name in pivot (e.g. summary, body, etc),
        * *selector*: CSS3 selector of the markup to extract.
        * *extract_image*: if is an image field to extract (possible parameters 0/1)
        * *remove*: CSS3 selectors of html which should be removed from the HTML markup
        * *clean_from_icons*: if is a text field and contains language selector icons on links which should be removed (possible parameters 0/1)
* **Antonov/config/entity_types/menu.json**: 
    * Menu entity configuration file with for extract the links:        
        * *menu_selector*: CSS3 Selectors to menu links.
        * *menu_plain*: Sets the menu to be extracted as plain menu with no depth or with depth (possible parameters 1 for service_tools / 0 for main_menu)
