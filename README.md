# Pico Navigation Plugin

This is a Plugin for the Flat File Based CMS named [Pico](pico.dev7studios.com/).

## use

Copy the `lmk_navigation.php` File to the `plugins` Folder in the Root of your Pico Project. Every Plugin in this Folder is activated automatically.

In your **theme** you only have to add this line where your navigation should be:

    {{ lmk_navigation.navigation }}

### set id and class of navigation element

Add these two lines to your `config.php` to change **id** and/ or **class** from `lmk-navigation` to anything you want:

```
$config['lmk_navigation']['id'] = 'lmk-navigation';
$config['lmk_navigation']['class'] = 'lmk-navigation';
```

### set class of list items and links

```
$config['lmk_navigation']['class_li'] = 'list-item';
$config['lmk_navigation']['class_a'] = 'link-item';
```

### exclude pages and folders
Add these two lines to your `config.php` to exclude **single pages** and/ or **folders**:

```
$config['lmk_navigation']['exclude']['single'] = array('a/site', 'another/site');
$config['lmk_navigation']['exclude']['folder'] = array('a/folder', 'another/folder');
```

## what it does

This Plugin generates a better navigation with child navigations and editable configuration.

So the output looks like:

    <ul id="lmk-navigation" class="lmk-navigation">
        <li><a href="…" title="…">…</a></li>
        <li>
            <a href="…" title="…">…</a>
            <ul>
                <li class="is-active"><a href="#" class="is-active" title="…">…</a></li>
            </ul>
        </li>
    </ul>

As you can see it will add an `.is-active` class to the `<a>` and `<li>` element of the **active page**.


## licence

CreativeCommons2.0 licence: [CC BY-SA](http://creativecommons.org/licenses/by-sa/2.0/)

You are free to share & remix this code only if you mention me as coder of this base.


## copyright

**Copyright © Ahmet Topal 2013. All rights reserved.**
