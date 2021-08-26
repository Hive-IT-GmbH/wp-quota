# WP-CLI Quota Command 
WP-CLI Command to easily manage quota in WordPress Multisite environments

### List quota
`wp quota list`
`[--fields] [--format] [--url]`

```bash
> wp quota list
+---------+--------------------------------------------------------+--------------+----------------+---------------------+
| blog_id | url                                                    | quota        | quota_used     | quota_used_percent  |
+---------+--------------------------------------------------------+--------------+----------------+---------------------+
| 1       | https://dev-site.local                                 | 5000         | 2500           | 50                  |
| 2       | https://subsite.local                                  | 12500        | 6230           | 49.84               |
| 3       | https://dev-site.local/subsite3                        | 10000        | 9850           | 98.50               |
+---------+--------------------------------------------------------+--------------+----------------+---------------------+
```
Using --url=\<domain\> is an alias for `wp quota get <id>`

### Get quota for single site
`wp quota get <id>`
`[--fields] [--format]`
Returns the quota information for a single site
```bash
> wp quota get 3
> wp quota list --url=dev-site.local/subsite3
+---------+--------------------------------------------------------+--------------+----------------+---------------------+
| blog_id | url                                                    | quota        | quota_used     | quota_used_percent  |
+---------+--------------------------------------------------------+--------------+----------------+---------------------+
| 3       | https://dev-site.local/subsite3                        | 10000        | 9850           | 98.50               |
+---------+--------------------------------------------------------+--------------+----------------+---------------------+
```

### Set Quota
`wp quota set <id> <quota-in-mb>`

`wp quota set <quota-in-mb> --url=<domain>`

Sets the quota for the chosen site to the given value
```bash
> wp quota set 2 10000
> wp quota set 10000 --url=subsite.local

Quota is now 10000 MB for subsite.local 
```

### Increase Quota
`wp quota add <id> <quota-to-add-in-mb>`

`wp quota add  <quota-to-add-in-mb> --url=<domain>`

Adds the given amount of quota to the chosen site
```bash
> wp quota add 2 3500
> wp quota add 3500 --url=subsite.local

Quota is now 16000 MB for subsite.local 
```

### Decrease Quota
`wp quota subtract <id> <quota-to-add-in-mb>`

`wp quota subtract <quota-to-subtract-in-mb> --url=<domain>`

Subtracts the given amount of quota from the chosen site
```bash
> wp quota subtract 2 2500
> wp quota subtract 2500 --url=subsite.local

Quota is now 10000 MB for subsite.local 
```

