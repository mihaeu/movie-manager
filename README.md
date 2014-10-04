# Movie-Manager

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

 - [ ] better README/docs
 - [ ] phar file
 - [ ] test coverage
 - [ ] same filters for all commands