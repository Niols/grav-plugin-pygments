Pygments ([Grav] plugin)
========================

- Because I <3 [Grav], but I also <3 [Pygments].

- Because I </3 Javascript and thus don't want to use a Javascript-based syntax
  highlighter.

- Because I wanted to be able to have a nicer syntax than `'''PHP`, and a
  syntax to specify a title (in the way the [Archlinux' Wiki][Archlinux-Wiki]
  does) and to highlight particular lines.

- Because I wanted to test Grav's plugins system.


What does it do?
----------------

Once installed, the plugin will post-process every page and look for codes in
the page. To avoid interacting with other plugins, it only processes the codes
begining with Grav-style headers, something like:

    Aut doloremque est vitae et dolor. Error omnis autem distinctio. Officia
	molestias neque temporibus quia numquam voluptatum. Ut veniam repudiandae
	occaecati. Porro architecto recusandae accusantium.
	
	    ---
		language: PHP
		highlights: [5, 8, 10]
		title: /home/niols/public_html/index.php
		---
		
		<?php
		echo 'Hello, World!';
	
	Qui impedit quis dolorem repellat suscipit voluptas corporis cum. Nostrum
	consequatur eaque veniam ipsa. Animi corrupti quis ratione voluptatem ut
	rerum ullam. Laborum ipsam dicta et aperiam libero iusto. Doloremque id
	animi vel. Ea nostrum cum facilis tempore iusto incidunt voluptas.

The Grav-style headers must be a Yaml value between two `---`. The supported
headers are:

- `language`: one of [the languages supported by Pygments][Pygments-languages].
  If not provided, Pygments will try to guess.

- `highlights`: a list of lines to highlight.

- `title`: a title to add before the code.

- `file`: if provided, the content of the given file will be taken instead of
  the body of the code.


[Archlinux-Wiki]: https://wiki.archlinux.org/
[Grav]: http://getgrav.org/
[Pygments]: http://pygments.org/
[Pygments-languages]: http://pygments.org/languages/
