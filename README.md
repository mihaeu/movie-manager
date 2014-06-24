# Movie-Manager

## Mission Statement

So I just decided to rewrite an old project of mine. Actually it's a rewrite of two to three projects only one of which was developed in a proper way and will be included as a dependency.

What is it supposed to do?

**Organize movies and produce good-looking output of the resulting movie-collection.**

So the app has two parts:

1) organize movies (find, format, rename, complete, etc.) into a collection

2) *print* collection (csv file, html file)

## Design Decisions

Keep in mind that:

- this should probably **not** have been done with PHP
- this is an oppinionated app for my purpose
- however it should be usable by others and extensible

### 1) Movie Organizer

This part requires a GUI and since this is PHP it'll be in the form of a web-site. This is far from optimal, but due to my current setup this is what it'll have to be (Iranian internet is so slow, that setting up a proper Java environment would take ages).

The work the movie organizer itself has to do can be split up into three main parts:

1) find and gather information from local movie files (are subtitles available, quality etc.)

2) match those movies against a movie database (e.g. tmdb, imdb, ...)

3) save movie information

The design for 1) and 2) should be pretty straight forward, but for 3) there is the question of how to save the data. The two obvious options are to save all the movie info in one place or per movie. For portability I prefer to keep the information separate for every movie.

Now I hate it when my movie folders are cluttered so I don't want to use another unnecessary file. My solution is to use the windows .url format, which is very easy to parse (.ini format) and human readable AND which serves as a link to the movie entry on the IMDb website.

What I want to store:

- original file information (helpful to find subtitles for a specific movie release or quality information (e.g. 480p/720p/1080p/divx etc.)
- IMDb link information
- as much movie information as possible (from all movie databases that we hit), that way we can later choose the information that we need to extract without having to hit network connections again

#### 1.1) Find movies

Find all movies in the given directory by

- [x] file extension (using my movie finder from the sub-collector)
- [ ] file size
- [ ] filters (e.g. no sample movie files)

and gather information about:

- [ ] is the movie in a separate folder or a plain file under the root directory (because we want to have all the movies in a separate folder) (*)
- [ ] does the movie have one or more subtitles
- [ ] does the movie have a release info file (.nfo)
- [ ] does the movie have a .url file and is it in our format with all the information (*)
- [ ] does the movie have a poster (*)
- [ ] what resolution is the movie in
- [ ] what other files are in the movie folder

(*) if these criterias are met, then the file has already been processed

#### 1.2) Match movies

First the user needs to provide a query to search for movie matches. This is done by providing a text input field and clickable tags which are created from the movie's filename (e.g. `Remember.me.720p.YIFY.mp4` => `Remember` `me`).

This query will be run against an internet movie database and the suggestions displayed.

The unique id will be the IMDb ID, because it is supported by most other databases as well. Using this ID we will query for more specific movie information.

#### 1.3) Save movie information

Write it to a .url file using ini format.

### 2) Collection Printer

This is pure-backend work and involves pretty much producing output from the information of the movie collection. This could actually be a separate project, but I'm not sure how reusable it would be.

For now a simple csv file and a nicer looking html with search/filter functionality will do.

This part of the app is trivial and involves converting the data and passing it to a templating engine.

`Twig` is the php templating engine of choice and I'll use it for the html output, but the csv can be done using the plain php csv functions.

Steps involved:

1) read movie collection information (keep this as abstract as possible!)

2) from this information write the appropriate output