# The Boulders Cookbook (A Wordpress Theme)
Wordpress theme for cookbooks with a development environment powered by docker, gulp, node, and composer.

### Installing The Dev Environment
_Requirements: node, gulp, docker, and composer_
1. Clone the repository locally. `git clone https://github.com/jamacon36/boulders-cookbook--wp .`
2. Install node dependencies `npm i`
3. Install local environment `composer install`
4. Run docker as daemon `docker-composer up -d`
5. Run the gulp pipeline `gulp`
6. Run the worpdress setup
7. Activate the Timber, ACF, and Gutenberg WordPress plugins