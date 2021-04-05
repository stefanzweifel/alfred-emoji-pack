# Alfred Emoji Pack

PHP Script to generate Snippets for [Alfred](https://www.alfredapp.com/) with all the latest available Emojis. (Currently includes up to Emoji 13.1 characters)

![Integrate](https://github.com/stefanzweifel/alfred-emoji-pack/workflows/Integrate/badge.svg)

## Inspiration
This project is heavily inspired by [califa/alfred-emoji-pack](https://github.com/califa/alfred-emoji-pack). (I helped adding a build step in [#1](https://github.com/califa/alfred-emoji-pack/pull/1)).
However, I'm more comfortable in PHP than in JavaScript. That's how this project was born.

My goal with this project is to automatically generate an always up-to-date Emoji pack for Alfred. A future version will use the [Unicode](https://unicode.org) website as its source for the available emojis.

## Usage
Download the file "Emoji Pack.alfredsnippets" from the latest [release](https://github.com/stefanzweifel/alfred-emoji-pack/releases) and open the file in Alfred.

## When are new Emojis added?
The project currently depends on [github/gemoji](https://github.com/github/gemoji). New Emojis are added whenever `github/gemoji` receives a new update.

The update cycle for `github/gemoji` is quite slow, so I would like to skip it entirely. I've started working on a project ([stefanzweifel/php-emojis](https://github.com/stefanzweifel/php-emojis
)) which creates a library of PHP classes based on the available emojis on the [Unicode](https://unicode.org/Public/emoji/) website. 

As soon as `stefanzweifel/php-emojis` reaches a stable version, this project will be updated to use that library.

## Local Development

The project requires PHP 8,.

1. Clone the repository
2. Install PHP dependencies with `composer install`
3. Do your thing ✨

## Generate New Pack

To generate a new pack or build, the PHP dependencies need to be installed.
Then run the following command in your terminal.

```
php app generate
```

The generated pack is located in the root of the project as "Emoji Pack.alfredsnippets".

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/stefanzweifel/alfred-emoji-pack/tags).

## Credits

* [Stefan Zweifel](https://github.com/stefanzweifel)
* [All Contributors](https://github.com/stefanzweifel/alfred-emoji-pack/graphs/contributors)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
