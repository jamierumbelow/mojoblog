# MojoBlog

MojoBlog is a formerly commercial MojoMotor blogging add-on. It's the number one MojoMotor add-on and has been hugely popular and (dare I say it) instrumental in helping MojoMotor adoption. MojoBlog is a small, lightweight and simple blogging platform that sticks to the MojoMotor principles of simplicity and ease-of-use.

After much deliberation, I decided to retire my add-ons at [Sparkplugs](http://getsparkplugs.com). I'm open sourcing MojoBlog under the MIT license for the community so that it can remain future-version compatibile and hopefully it will develop and grow into a bigger and better add-on.

## Synopsis

    {mojo:blog:entries page="about|home" limit="10" status="published" orderby="date" sort="desc" date_format="Y-m-d" paginate="yes" per_page="5" pagination_segment="p"}
        {entries}
            <h1>{title}</h1>
            <p>{content}</p>
        {/entries}

        {pagination}{first_page_url} {prev_page_url} - Page {current_page} of {total_pages} - {next_page_url} {last_page_url}{/pagination}
    {/mojo:blog:entries}

## Installation

Installing MojoBlog is just like any other MojoMotor addon. Simply drag the blog/ folder found in your download into the _system/mojomotor/third_party/_ folder. Ensure that your *config/config.php* and *config/routes.php* files are set to have **0777** permissions. Then, point your web browser to **http://domain.com/index.php/admin/addons/blog/install** and MojoBlog will install itself.

## Usage

Complete usage documentation is available in the repo. I want to convert it all over to Markdown and chuck in this repo's wiki.

## Changelog

**Version 2.2.0**
* Adding the category="" parameter
* Updates to documentation
* Status parameter now defaults to published
* Removal of hard-coded table names
* Entries are now listed in date-descending order

**Version 2.1.1**
* Fixed a CSS issue with MM1.1.1
* Fixed an error with different language browsers

**Version 2.1.0**
* Adding the {excerpt} variable
* Fixing an error with file URLs

**Version 2.0.0**
* Added a new MojoBar interface
* Added a polished install and update interface
* Added entry statuses
* Added categories
* Added entry URL titles
* Added a tag for Disqus integration
* Added an {if single_entry_page} conditional
* Thoroughly cleaned up the code
* Fixed many bugs

**Version 1.1.3**
* Moved the install procedure into a separate method callable from the URL
* Removed the entry_id_segment parameter and added the entry_id_qs parameter to get round MM1.1
* Moved pagination over to using querystrings
* Fixed an error with page restrictions

**Version 1.1.2**
* Added attempted compatibility for MM1.1

**Version 1.1.1**
* Fixed an error with multi-line regexes and made all the tags case insensitive
* Fixed MM1.1 compatibility
* Added an {excerpt} variable

**Version 1.1.0**
* Added Outfielder support
* Added Pagination
* Added an {author} tag
* entry_id, no_posts_404 and entry_id_segment parameters (single entry pages)
* Fixed a language file bug
* Fixed a bug with apostrophes
* Fixed MojoMotor save button error
* Added a title="" parameter

**Version 1.0.0**
* First Release