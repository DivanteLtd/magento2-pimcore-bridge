1.0.11
=============
- asset request endpoint has been changed to get optimized images

1.0.10.2
=============
- fix asset queue id data type

1.0.10.1
=============
- update media gallery types to make it open for plugins

1.0.10
=============
- change asset id column type int -> varchar

1.0.9.4
=============
- fix broken translation for attributes labels

1.0.9.3
=============
- add description and short description to excluded attributes

1.0.9.2
=============
- refactor creating visual swatch

1.0.9.1
=============
- visual swatch can be set as a configurable attribute

1.0.9
=============
- now supports also visual swatches

1.0.8.1
=============
- add missing data transformator for a quantityValue type attribute

1.0.8
=============
- add quantity value strategy type

1.0.7
=============
* add price override configuration `configuration/prices/is_override_enabled`. If yes then price from Pimcore will override current price, otherwise price will be set only on the first one.

1.0.6.0
=============
* now it is possible to configure attribute data from a Pimcore. Additional informations kept in 'attr_conf' key in attribute will be merged and override default configuration of an attribute. `\Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy\AbstractStrategy::getMergedConfig`

1.0.5.5
=============
* add .gitignore
* update console commands to be compatible with 2.3.x

1.0.5.4
=============
* GitHub PR:
    * [#22](https://github.com/DivanteLtd/magento2-pimcore-bridge/pull/22) -- Api interfaces return fix for proper functioning of swagger

1.0.5.3
=============
* GitHub PR:
    * [#21](https://github.com/DivanteLtd/magento2-pimcore-bridge/pull/21) -- Commands dependencies changes to factories

1.0.5.2
=============
* Module name change for Packagist connections
* Magento2 version compatibility update

1.0.5.1
=============
* Update version to work with new Pimcore

1.0.4.13
=============
* Initial commit
