<img src="https://www.naxero.com/professional-ecommerce-integrations-for-magento.jpg" alt="Naxero.com"/>

## Narero.com Translation and CSV files manager for Magento 2

The Naxero Translation module for Magento 2 allows shop owners to translate Magento 2 language strings stored in CSV files without technical knowledge.

The user interface gives read and write access to all CSV documents available in the file system, allowing administrators to manage CSV files in a few clicks.

Search, view, edit, translate and scan all CSV files in a few clicks without technical knowledge through the administration panel.

## Compatibility
The Naxero Translation Module for Magento 2 is compatible with Magento 2.2.0 and above.

## Features
The Naxero Translation Module for Magento 2 offers useful and unique features, allowing Magento 2 shop owners manage all CSV language files from a unique entry point, increasing efficiency and productivity when creating multi-lingual Magento 2 websites.

Amongst many others, the major features are: 

* View a list of all CSV languague files
* Veiw the content of all CSV languague files
* Edit files from the administration interface
* Automatically save changes on the fly
* Scan the file system to index newly added files
* Detect errors irregularities in language CSV files
* Eeasily create or copy CSV files to a new location

## Installation
The easiest and recommended way to install the Naxero Translation Module for Magento 2 is to run the following commands in a terminal, from your Magento 2 root directory:

```bash
composer require naxero/translation:*
bin/magento setup:upgrade
rm -rf var/cache var/generation/ var/di
bin/magento setup:di:compile && php bin/magento cache:clean
```

## Update
In order to update the Naxero Translation Module for Magento 2, please run the following commands in a terminal, from your Magento 2 root directory:

```bash
composer update naxero/translation:*
bin/magento setup:upgrade
rm -rf var/cache var/generation/ var/di
bin/magento setup:di:compile && php bin/magento cache:clean
```

For more information on the Magento 2 modules installation process, please have a look at the [Magento 2 official documentation](http://devdocs.magento.com/guides/v2.0/install-gde/install/cli/install-cli-subcommands-enable.html "Magento 2 official documentation")

## Configuration
Once the Checkout.com extension for Magento 2 installed, go to **Stores > Configuration > Naxero > Translation** to see the configuration and customization options available. 

The follwin parameters are availale in the module configuration:

Dedicated technical support is available to all Merchants using the Naxero Translation Module for Magento 2 via the GitHub repositories or directly by email at contact@naxero.com. Naxero.com does not provide support for third party plugins or any alterations made to the official Naxero.com plugins.

**DISCLAIMER**
In no event shall Naxero.com be liable for any claim, damages or other liability, whether in an action of contract, tort or otherwise, arising from the information or code provided or the use of the information or code provided. This disclaimer of liability refers to any technical issue or damage caused by the use or non-use of the information or code provided or by the use of incorrect or incomplete information or code provided.
