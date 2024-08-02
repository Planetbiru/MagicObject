### Pageable

**PicoPageable**

Constructor:

Parameters:

- PicoPage|PicoLimit|array $page
- PicoSortable|array $sortable

Method:

- getSortable
- setSortable
- addSortable
- createOrderBy
- getPage
- setPage
- getOffsetLimit
- setOffsetLimit

**PicoPage**

Constructor:

Parameters:

- integer $pageNumber
- integer $pageSize

Metods:

- getPageNumber
- setPageNumber
- getPageSize
- setPageSize

**PicoLimit**

Constructor:

Parameters:

- integer $offset
- integer $limit

Metods:

- getOffset
- setOffset
- getLimit
- setLimit

Example:


**With Page**

```php
$pageable = new PicoPageable(new Page(1, 100));
// page number = 1
// page size = 100
// no sortable
```

**With Page and Sortable**

```php
$sortable = new PicoSortable();

$sort1 = new PicoSort('userName', 'asc');
$sort2 = new PicoSort('email', 'desc');
$sort3 = new PicoSort('phone', 'asc');

$sortable->add($sort1);
$sortable->add($sort2);
$sortable->add($sort3);

$pageable = new PicoPageable(new Page(1, 100), $sortable);
// page number = 1
// page size = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('userName', 'asc'))
    ->add(new PicoSort('email', 'desc'))
    ->add(new PicoSort('phone', 'asc'))
;
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('userName', PicoSort::ORDER_TYPE_ASC))
    ->add(new PicoSort('email', PicoSort::ORDER_TYPE_DESC))
    ->add(new PicoSort('phone', PicoSort::ORDER_TYPE_ASC))
;

$pageable = new PicoPageable(new Page(1, 100), $sortable);
// page number = 1
// page size = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

**With Limit and Sortable**

```php
$sortable = new PicoSortable();

$sort1 = new PicoSort('userName', 'asc');
$sort2 = new PicoSort('email', 'desc');
$sort3 = new PicoSort('phone', 'asc');

$sortable->add($sort1);
$sortable->add($sort2);
$sortable->add($sort3);

$pageable = new PicoPageable(new PicoLimit(0, 100), $sortable);
// page limit = 100
// page offset = 0
// ORDER BY user_name ASC, email DESC, phone ASC
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('userName', 'asc'))
    ->add(new PicoSort('email', 'desc'))
    ->add(new PicoSort('phone', 'asc'))
;
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('userName', PicoSort::ORDER_TYPE_ASC))
    ->add(new PicoSort('email', PicoSort::ORDER_TYPE_DESC))
    ->add(new PicoSort('phone', PicoSort::ORDER_TYPE_ASC))
;

$pageable = new PicoPageable(new PicoLimit(0, 100), $sortable);
// page limit = 100
// page offset = 0
// ORDER BY user_name ASC, email DESC, phone ASC
```

1. Construtor with page as PicoPageable and sortable as PicoSortable

`$pageable = new PicoPageable(new PicoPage(1, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 0`

`$pageable = new PicoPageable(new PicoPage(3, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 200`


2. Construtor with page as PicoPage and sortable as array

`$pageable = new PicoPageable(new PicoPage(1, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 0`

`$pageable = new PicoPageable(new PicoPage(5, 50), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 50 OFFSET 200`

3. Construtor with page as PicoLimit and sortable as PicoSortable

`$pageable = new PicoPageable(new PicoLimit(0, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 0`

`$pageable = new PicoPageable(new PicoLimit(50, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 50`

4. Construtor with page as PicoLimit and sortable as array

`$pageable = new PicoPageable(new PicoLimit(0, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 0`

`$pageable = new PicoPageable(new PicoLimit(20, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 20`

5. Construtor with page as array and sortable as PicoSortable

`$pageable = new PicoPageable(array(10, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 900`

6. Construtor with page as array and sortable as array

`$pageable = new PicoPageable(array(3, 200), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 200 OFFSET 400`
