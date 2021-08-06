# Media Storage Sync
*This project is a fork of [this repo](https://github.com/PHOENIX-MEDIA/magento2-mediastoragesync). Since it was no longer maintained, and contained critical bugs, we continue maintaining it here in this fork, under a new name. Kudos to [Phoenix Media](https://www.phoenix-media.eu/de/) for their work.* 

The module retrieves files in /media from an origin server/url. Useful if you have a local development environment for your shop, and don't want to download the whole media folder along with the shop. 

This module will try to download the images from the provided url, as they are required. 

## What it does

Imagine you have a fresh local development environment with the Magento code checked out.
You retrieved the database but you don't have any of the media assets and your store frontend
just looks incomplete. You could grab a huge archive of the media folder from the production
environment but no one really wants to download tens of gigabytes just to work on a few catalog
pages.

This modules implements some plugins and observers that downloads images of categories,
products and CMS blocks/pages from a configurable origin server similar to a CDN the first time
you load an entity from the database. This means you can forget about the media folder and
just browse the frontend as images are downloaded and saved transparently.

## How it works

In the module's configuration you can configure a base URL, the domain where your production/staging
Magento instance is located from which to picked the database. In the database the relative
paths of categories and product images are stored. Once those entities are loaded the module
simply checks if their images are already in media/catalog. If not it uses the base URL,
appends the relative image path from the database and downloads the files from origin server.
This slows down page generation the first time you access a page but improves pretty quickly.

## Settings

- `Enable` - Self explanatory
- `URL` - The url full url of the shop you want to download medias from (e.g. https://yoursite.com/)
- `HTTP Client User`- Username, in case of need for authorization 
- `HTTP CLient Password`- Password, in case of need for authorization
- `Download Limit Per Request`- Limit the amount of downloads per request

## Developer informations

### Install module

1. Install the module via Composer:
    ``` 
    composer require salecto2/magento2-mediastoragesync
    ```
2. Enable it

    ```
    php bin/magento module:enable Salecto_MediaStorageSync
    ```

3. Install the module and rebuild the DI cache

    ```
    php bin/magento setup:upgrade
    ```



### How to configure

Find the modules configuration in the PHOENIX MEDIA section of your Magento configuration.

Enable: Enable or disable the functionality

URL: Configure the source URL where to retrieve the images (e.g. "https://magento.com/")

optionally configure credentials for BasicAuth.
