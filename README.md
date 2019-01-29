# Open source appointment planer

This is an alternative to calendly with support for CalDAV (Nextcloud).

Live version here: [](https://termin.sebastian-clemens.de)

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

You need Composer and Node.js for installing.

### Installing

A step by step series of examples that tell you how to get a development env running

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
npm install
npm run build
```

You can also run `npm start` for watch and browser sync services. Root your webserver to public folder and you're done!

## Built With

* [Slim](https://github.com/slimphp/Slim) - The php framework used

## Authors

* **Sebastian Clemens** - [me on GitHub](https://github.com/sebclemens)
