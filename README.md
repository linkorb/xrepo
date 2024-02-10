xrepo
=====

Cross-repo tools

## Installation

```
$ git clone ...
$ cd xrepo

# install php dependencies
$ composer install

# create configuration
$ cp .env.dist .env
$ vim .env
```

## Usage

```sh
# List available sub-commands
$ bin/xrepo list

# Index repos, scanning XREPO_CODE_PATH recursively for git repos. Output cached in XREPO_DATA_PATH/index.json
$ bin/xrepo index

# Show (optionally filtered) repos in cache
$ bin/xrepo show

# Show repos where attribute repo.linkorb.com/license is mit
$ bin/xrepo show --limit repo.linkorb.com/license=mit
```

## License

Please refer to the included LICENSE.md file

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [engineering.linkorb.com](http://engineering.linkorb.com).

Btw, we're hiring!
