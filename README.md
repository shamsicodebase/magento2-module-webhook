**Note:** This is alpha software and not to be used in production until v1.0.0

# Webhooks for Magento 2

![Preview](https://s3.amazonaws.com/sweettooth-static/github/magento2-webhook-preview)

This module provides webhooks for Magento 2 events. Inspired by [Alan Kent's](https://twitter.com/akent99)'s [Webhooks in Magento 2](http://alankent.me/2015/05/13/webhooks-in-magento-2/) blog post which mentions a rough timeline for getting webhooks in Magento 2 core:

> Better support for webhooks is on the backlog, but currently not guaranteed for Magento 2 GA.

We're hopeful the community can help push this effort forward through this module.

## Getting Started

Install via composer
```
composer require sweettooth/magento2-module-webhook
```

Add `SweetTooth_Webhook` to your `app/etc/config.php`
```php
<?php
return array (
  'modules' => 
  array (
    //
    // Bunch of other modules
    // 
    'SweetTooth_Webhook' => 1,
  ),
);
```

Run database migrations
```
php bin/magento setup:upgrade
```

## Supported Webhooks

Available now
- Customer updated
- Customer deleted
- Order updated
- Order deleted
- Product updated
- Product deleted

TODO
- Customer created
- Order created
- Product created
- CRUD operations for other resources

## Roadmap and areas for discussion

### Async webhooks [(RFC)](https://github.com/sweettooth/magento2-module-webhook/issues/6)
Without async webhooks, this module is pretty much a no-go for production shops - the dependency on third party systems is just too risky to do synchronously. The best practice for performing tasks asynchronously is to queue it up on a memory store (redis, memcache, etc) and have a background worker pick up the job and perform it, meanwhile the synchronous request returns immediately. Since there's no native queueing for Magento 2, our best bet might be to use the database as a 'queue' ("Blasphemy!" you say. Chill, magento already does this in the newsletter module) then use the cron to pick up the jobs every minute.

### Serialization [(RFC)](https://github.com/sweettooth/magento2-module-webhook/issues/7)
Right now serializing the payload of the webhook is super basic, just calling `getData()` on the model. This is all kinds of bad because it will expose sensitive information like password hashes and such. A better strategy would be to create a serializer for each resource. An even better strategy is if we could re-use the serializer for the REST API so our webhook data has an identical json structure to the API. Boom.

### Extensibility
It would be really cool to make this module extendible so other modules could add events that can be webhook'd

### Creating webhooks through the API
This is a really legit use case. An app that has API access to a shop may want to register webhooks to receive CRUD events on specific resources that they would otherwise need to poll for every x hours. Both Shopify and Bigcommerce have this endpoint and it's lovely.

### Data formats
We should probably support XML someday. *sigh*

## Contributing

Submit a pull request!
