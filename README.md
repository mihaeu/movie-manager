# Movie-Manager
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mihaeu/movie-manager/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mihaeu/movie-manager/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/mihaeu/movie-manager/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mihaeu/movie-manager/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/mihaeu/movie-manager/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mihaeu/movie-manager/?branch=master)
[![SensioLabgs Insight](https://insight.sensiolabs.com/projects/779570d4-8dfa-4ab2-8c37-85f59a56c7b8/mini.png)](https://insight.sensiolabs.com/projects/779570d4-8dfa-4ab2-8c37-85f59a56c7b8)

> Movie manager for nerds (and people who suffer from OCD).

If you love movies as much as I do you probably have a massive collection of movies at home. Keeping those movies properly organized can be tedious if done by hand. This command line tool automates this task for you as much as possible. Think of it as Kodi (former XBMC) without all the other features and more portable.

## Feature Overview

 - **Organize** your collection with proper titles, posters, movie information
 - **Generate** beautiful standalone movie collections (single html file, with posters and filter functions)
 - **List** your movies with filters like *release date* or *rating* (e.g. list up to 100 GB of movies, released 2001, highest ranking first and copy them somewhere)

 This is especially awesome when combined with `xargs`. If I want to fill my 16gb pendrive with the newest and highest ratest movies I could do so in one line (broken up for readability):

```bash
bin/moviemanager print-list /tmp/movies \
--year-from 2015 \			# only movies from 2015
--max-size-movie 1000 \ 	# maximum size per movie
--max-size 16000 \ 			# maximum size of all movies together
--sort imdb_rating \		# sort by IMDb rating
--print0 \					# separate movies with a \0 instead of \n for xargs
| xargs -0 -I {} cp -a {} /mnt/pendrive
```

## Installation

I'll create a .phar file later, but for now `git clone` or `composer [global] require mihaeu/movie-manager:*` should do the trick.

## Usage

Just some usage examples (check the help command for each command for more information):

```bash
bin/moviemanager manage [--show-all] [--move-to="..."] path
bin/moviemanager list [--limit="..."] [--year-from="..."] [--year-to="..."] [--rating="..."] [--max-size="..."] [--sort-by="..."] [--desc] [--print0] path
bin/moviemanager build [--limit="..."] [--no-posters] path [save]
```

## TO DO

See [Issues](https://github.com/mihaeu/movie-manager/issues)