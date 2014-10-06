# Movie-Manager
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mihaeu/movie-manager/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mihaeu/movie-manager/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/mihaeu/movie-manager/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mihaeu/movie-manager/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/mihaeu/movie-manager/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mihaeu/movie-manager/?branch=master)
[![SensioLabgs Insight](https://insight.sensiolabs.com/projects/779570d4-8dfa-4ab2-8c37-85f59a56c7b8/mini.png)](https://insight.sensiolabs.com/projects/779570d4-8dfa-4ab2-8c37-85f59a56c7b8)

> Organize, list, search and filter your movie collection.

## Overview

 - **Organize** your collection with proper titles, posters, movie information
 - **List** your movies with filters like *release date* or *rating* (e.g. list up to 100 GB of movies, released 2001, highest ranking first and copy them somewhere)
 - **Generate** beautiful standalone movie collections (single html file, with posters and filter functions)

## Installation

I'll create a .phar file later, but for now `git clone` or `composer [global] require mihaeu/movie-manager:*` should do the trick.

## Usage

```bash
bin/moviemanager manage [--show-all] [--move-to="..."] path
bin/moviemanager list [--limit="..."] [--year-from="..."] [--year-to="..."] [--rating="..."] [--max-size="..."] [--sort-by="..."] [--desc] [--print0] path
bin/moviemanager build [--limit="..."] [--no-posters] path [save]
```

## TO DO

See [Issues](https://github.com/mihaeu/movie-manager/issues)
