# Greenrivers MagentoIntegration Bundle

### Installation

1. Copy the contents of this module to `bundles/Greenrivers/Bundle/MagentoIntegrationBundle`
2. Run `php bin/console pimcore:bundle:install GreenriversMagentoIntegrationBundle`

### Config

_Settings->Greenrivers->Magento Integration_

_General_

**Magento Url** - Magento base url (ending with slash)<br>
**Magento Token** - Magento integration token (Bearer auth)

_Magento_

**Send Product to Magento after save** - send product to Magento after saving DataObject in Pimcore<br>
**Send Category to Magento after save** - send category to Magento after saving DataObject in Pimcore

### Usage

Create product

1. Open folder: _Data Objects->Greenrivers->MagentoIntegration->Products_
2. _Add Object->Greenrivers->MagentoIntegrationProduct_
3. **Save & Publish** product.
4. Pimcore sends product to Magento.

Create category

1. Open folder: _Data Objects->Greenrivers->MagentoIntegration->Categories_
2. _Add Object->Greenrivers->MagentoIntegrationCategory_
3. **Save & Publish** category.
4. Pimcore sends category to Magento.

### Endpoints

https://app.pimcore.test/pimcore-graphql-webservices/greenrivers

Authorization: apikey **(GraphQL DataHub apikey)**

Get products

```graphql
{
    getMagentoIntegrationProductListing {
        edges {
            node {
                id
                status
                attributeSetId
                name
                sku
                price
            }
        }
    }
}
```

Get categories

```graphql
{
    getMagentoIntegrationCategoryListing {
        edges {
            node {
                id
                isActive
                includeInMenu
                name
                parentCategoryId
            }
        }
    }
}
```

### Errors

Errors are logged into **var/log/greenrivers/magento_integration.log** folder.

### Testing

Run tests:

(due to problem with null container in 2nd test in **SettingsControllerTest**, comment one test method before test)

```shell
 vendor/bin/simple-phpunit bundles/Greenrivers/Bundle/MagentoIntegrationBundle/tests
```

### Sources

https://pimcore.com/docs/platform/Datahub/GraphQL/#external-access

https://developer.adobe.com/commerce/webapi/get-started/authentication/gs-authentication-token/#integration-tokens
