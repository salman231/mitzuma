Magento 2 Shipping Matrix Rates by Mageside
===========================================

####Support
    v1.2.10 - Magento 2.1.* - 2.3.*

####Change list
    v1.2.10 - Updated composer for Magento 2.3.
    v1.2.9 - Fix for long Delivery Method Names.
    v1.2.8 - Made post code case insensitive.
    v1.2.7 - Added support postcode ranges for Ireland.
    v1.2.6 - Added "VARIANTS" logic
    v1.2.5 - Added translation for method title
    v1.2.4 - Added Magento Commerce (EE) support.
    v1.2.3 - Moved csv delimiter to default config. Added comments to configuration section.
    v1.2.2 - Added skipping rates with price = -1.
    v1.2.1 - Allowed customer group 'NOT LOGGED IN'.
    v1.2.0 - Improved functionality of OVER_WEIGHT, FULL_WEIGHT, OVER_ITEM logics.
             Added decrypted error message when duplicate records.
             Fixed postcode ranges for GBR.
             Updated validation-rules.js. Now only country is required.
    v1.1.0 - Added support postcode ranges for GBR.
    v1.0.7 - Added admin options for 'from' and 'to' filters operators.
    v1.0.5 - Magento 2.2 compatibility checking (updated composer.json). Some renames.
    v1.0.4 - Fixed issue placing order if shipping method name too long.
    v1.0.0 - Start project

####Installation
    1. Download the archive.
    2. Make sure to create the directory structure in your Magento - 'Magento_Root/app/code/Mageside/ShippingMatrixRates'.
    3. Unzip the content of archive to directory 'Magento_Root/app/code/Mageside/ShippingMatrixRates'
       (use command 'unzip ArchiveName.zip -d path_to/app/code/Mageside/ShippingMatrixRates').
    4. Run the command 'php bin/magento module:enable Mageside_ShippingMatrixRates' in Magento root.
       If you need to clear static content use 'php bin/magento module:enable --clear-static-content Mageside_ShippingMatrixRates'.
    5. Run the command 'php bin/magento setup:upgrade' in Magento root.
    6. Run the command 'php bin/magento setup:di:compile' if you have a single website and store, 
       or 'php bin/magento setup:di:compile-multi-tenant' if you have multiple ones.
    7. Clear cache: 'php bin/magento cache:clean', 'php bin/magento cache:flush'

####Troubleshooting
    1. Problem of uploading files on windows servers: https://github.com/magento/magento2/issues/3256  
