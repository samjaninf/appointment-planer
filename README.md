# Open source appointment planer

This is an alternative to calendly with support for CalDAV (Nextcloud). It finds open slots in your calendar and let visitors create new events at your shared link. 

Live version here: https://termin.sebastian-clemens.de

## Getting Started

### Prerequisites

You need Composer and Node.js for installing.

### Installing

Copy example env file and edit with your settings

```shell
cp .env.example.ini .env.example.ini
```

Next step let composer install php packages

```shell
composer install
```

Go into resources directory and let node.js install components and create assets

```shell
cd resources
npm install
npm run build
```

You can also run `npm start` for watch and browser sync services. Root your webserver to public folder and you're done!

## Built With

* [Slim](http://www.slimframework.com/) - a micro framwork for php
* [Foundation](https://foundation.zurb.com/) - the most advanced responsive front-end framework in the world

## Authors

* **Sebastian Clemens** - [me on GitHub](https://github.com/sebclemens)
