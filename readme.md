# Custom 404 Error Page

Set any page to be used as the 404 error page under **"Settings > Reading"**.

## Contribute

### Release

- Update version in `custom-404-page.php` and `readme.txt`.
- Add changelog entry to `readme.txt` using semantic versioning.
- Run `svn co https://plugins.svn.wordpress.org/custom-404-error-page/ svn` to checkout the SVN repository.
- Run `rsync -aiv --delete --delete-excluded --exclude-from=.distignore . svn/tags/0.2.6` to create the tag or `rsync -aiv --delete --delete-excluded --exclude-from=.distignore . svn/trunk/` to update `trunk`.
- Run `rsync -aiv --delete --delete-excluded --exclude-from=.distignore assets/ svn/assets/` to commit the assets.

## Credits

Created by [Kaspars Dambis](https://kaspars.net).
