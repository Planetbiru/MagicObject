## Entity

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="album")
 */
class Album extends MagicObject
{
	/**
	 * Album ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Album ID")
	 * @var string
	 */
	protected $albumId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Title
	 * 
	 * @Column(name="title", type="text", nullable=true)
	 * @Label(content="Title")
	 * @var string
	 */
	protected $title;

	/**
	 * Description
	 * 
	 * @Column(name="description", type="longtext", nullable=true)
	 * @Label(content="Description")
	 * @var string
	 */
	protected $description;

	/**
	 * Producer ID
	 * 
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Release Date
	 * 
	 * @Column(name="release_date", type="date", nullable=true)
	 * @Label(content="Release Date")
	 * @var string
	 */
	protected $releaseDate;

	/**
	 * Number Of Song
	 * 
	 * @Column(name="number_of_song", type="int(11)", length=11, nullable=true)
	 * @Label(content="Number Of Song")
	 * @var integer
	 */
	protected $numberOfSong;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var double
	 */
	protected $duration;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
	 * @Label(content="Sort Order")
	 * @var integer
	 */
	protected $sortOrder;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $active;

	/**
	 * As Draft
	 * 
	 * @Column(name="as_draft", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $asDraft;

}
```

Strategy to generate auto value:

**1. GenerationType.UUID**

Generate 20 bytes unique ID

- 14 byte hexadecimal of uniqid https://www.php.net/manual/en/function.uniqid.php
- 6 byte hexadecimal or random number

**2. GenerationType.IDENTITY**

Autoincrement using database feature

MagicObject will not update `time_create`, `admin_create`, and `ip_create` because `updatable=false`. So, even if the application wants to update this value, this column will be ignored when performing an update query to the database.

### Usage

```php
<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseCredentials;
use MusicProductionManager\Config\ConfigApp;

use MusicProductionManager\Config\ConfigApp;

use MusicProductionManager\Data\Entity\Album;

require_once dirname(__DIR__)."/vendor/autoload.php";

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(dirname(__DIR__)."/.cfg/app.yml", true, true);

$databaseCredentials = new PicoDatabaseCredentials($cfg->getDatabase());
$database = new PicoDatabase($databaseCredentials);
try
{
    $database->connect();
  
    // create new 
  
    $album1 = new Album(null, $database);
    $album1->setAlbumId("123456");
    $album1->setName("Album 1");
    $album1->setAdminCreate("USER1");
    $album1->setDuration(300);
  
  
  
    // other way to create object
    // create object from stdClass or other object with match property (snake case or camel case)
    $data = new stdClass;
    // snake case
    $data->album_id = "123456";
    $data->name = "Album 1";
    $data->admin_create = "USER1";
    $data->duration = 300;
  
    // or camel case
    $data->albumId = "123456";
    $data->name = "Album 1";
    $data->adminCreate = "USER1";
    $data->duration = 300;
  
    $album1 = new Album($data, $database); 
  
    // other way to create object
    // create object from associated array with match property (snake case or camel case)
    $data = array();
    // snake case
    $data["album_id"] "123456";
    $data["name"] = "Album 1";
    $data["admin_create"] = "USER1";
    $data["duration"] = 300;
  
    // or camel case
    $data["albumId"] = "123456";
    $data["name"] = "Album 1";
    $data["adminCreate"] = "USER1";
    $data["duration"] = 300;
    $album1 = new Album($data, $database);
  
  
    // get value from form
    // this way is not safe
    $album1 = new Album($_POST, $database);
  
  
    // we can use other way
    $inputPost = new InputPost();
  
    // we can apply filter
    $inputPost->filterName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
    $inputPost->filterDescription(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
  
    // if property not present in $inputPost, we can set default value
    // please note that user can modify form and add update any unwanted properties to be updated
    $inputPost->checkboxActive(false);
    $inputPost->checkboxAsDraft(true);
  
    // we can remove any property data from object $inputPost before apply it to entity
    // it will not saved to database
    $inputPost->setSortOrder(null);
  
    $album1 = new Album($inputPost, $database);
  
    // insert to database
    $album1->insert();
  
    // insert or update
    $album1->save();
  
    // update
    // NoRecordFoundException if ID not found
    $album1->update();
  
    // convert to JSON
    $json = $album1->toString();
    // or
    $json = $album1 . "";
  
    // send to buffer output
    // automaticaly converted to string
    echo $album1;
  
    // find one by ID
    $album2 = new Album(null, $database);
    $album2->findOneByAlbumId("123456");
  
    // find multiple
    $album2 = new Album(null, $database);
    $albums = $album2->findByAdminCreate("USER1");
    $rows = $albums->getResult();
    foreach($rows as $albumSaved)
    {
        // $albumSaved is instance of Album
  
        // we can update data
        $albumSaved->setAdminEdit("USER1");
        $albumSaved->setTimeEdit(date('Y-m-d H:i:s'));
  
        // this value will not be saved to database because has no column
        $albumSaved->setAnyValue("ANY VALUE");
  
        $albumSaved->update();
    }
  
  
}
catch(Exception $e)
{
    // do nothing
}

```


### Insert

Insert new record

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminCreate("USER1");
$album1->setDuration(300);
try
{
	$album->insert();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

To insert with any column value `NULL`

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminCreate("USER1");
$album1->setDuration(300);
$album1->setReleaseDate(null);
$album1->setNumberOfSong(null);
try
{
	// releaseDate and numberOfSong will set to NULL
	$album->insert(true);
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Save

Insert new record if not exists, otherwise update the record

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminCreate("USER1");
$album1->setAdminEdit("USER1");
$album1->setDuration(300);
try
{
	$album->save();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

Note: If operation is update, nonupdatable column will not be updated

### Update

Update existing record

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminEdit("USER1");
$album1->setDuration(300);
try
{
	$album->update();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

To update any column value to `NULL`

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminEdit("USER1");
$album1->setDuration(300);
$album1->setReleaseDate(null);
$album1->setNumberOfSong(null);
try
{
	// releaseDate and numberOfSong will set to NULL
	$album->update(true);
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Select One By Specific Column

```php
$album1 = new Album(null, $database);
try
{
	$album1->findOneByAlbumId("123456");

	// to update the record

	// update begin
	$album1->setName("Album 1");
	$album1->setAdminEdit("USER1");
	$album1->setDuration(300);
	$album->update();
	// update end

	// to delete the record

	// delete begin
	$album->delete();
	// delete end
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Select One By Combination of Columns

Logic `OR`

```php
$album1 = new Album(null, $database);
try
{
	$album1->findOneByAlbumIdOrNumbefOfSong("123456", 3);

	// to update the record

	// update begin
	$album1->setAdminEdit("USER1");
	$album1->setDuration(300);
	$album->update();
	// update end

	// to delete the record

	// delete begin
	$album->delete();
	// delete end
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

Logic `AND`

```php
$album1 = new Album(null, $database);
try
{
	$album1->findOneByAdminCreateAndNumbefOfSong("USER1", 3);

	// to update the record

	// update begin
	$album1->setAdminEdit("USER1");
	$album1->setDuration(300);
	$album->update();
	// update end

	// to delete the record

	// delete begin
	$album->delete();
	// delete end
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Select Multiple By Combination of Columns

Logic `OR`

```php
$albumSelector = new Album(null, $database);
try
{
	$albums = $albumSelector->findByAlbumIdOrNumbefOfSong("123456", 3);
	
	$result = $albums->getResult();
	foreach($result as $album1)
	{
		// to update the record

		// update begin
		$album1->setAdminEdit("USER1");
		$album1->setDuration(300);
		$album->update();
		// update end

		// to delete the record

		// delete begin
		$album->delete();
		// delete end
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

Logic `AND`

```php
$albumSelector = new Album(null, $database);
try
{
	$albums = $albumSelector->findOneByAdminCreateAndNumbefOfSong("USER1", 3);
	
	$result = $albums->getResult();
	foreach($result as $album1)
	{
		// to update the record

		// update begin
		$album1->setAdminEdit("USER1");
		$album1->setDuration(300);
		$album->update();
		// update end

		// to delete the record

		// delete begin
		$album->delete();
		// delete end
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Find By Specification

Real applications do not always use simple logic to filter database records. Complex logic cannot be done using just one method. MagicObject provides features to make searches more specific.

**Example 1**

```php
$album = new Album(null, $database);
$rowData = array();
try
{
	$album->findOneByAlbumId($inputGet->getAlbumId());

	$sortable = new PicoSortable();
	$sort2 = new PicoSort('trackNumber', PicoSortable::ORDER_TYPE_ASC);
	$sortable->addSortable($sort2);

	$spesification = new PicoSpecification();

	$predicate1 = new PicoPredicate();
	$predicate1->equals('albumId', $inputGet->getAlbumId());
	$spesification->addAnd($predicate1);

	$predicate2 = new PicoPredicate();
	$predicate2->equals('active', true);
	$spesification->addAnd($predicate2);
	
	// Up to this point we are still using albumId and active

	$pageData = $player->findAll($spesification, null, $sortable, true);
	$rowData = $pageData->getResult();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}

if(!empty($rowData))
{
	foreach($rowData $album)
	{
		// do something here
		// $album is instanceof Album class
		// You can use all its features
	}
}
```

**Example 2**

Album specification from `$_GET`

```php

/**
 * Create album specification
 * @param PicoRequestBase $inputGet
 * @return PicoSpecification
 */
function createAlbumSpecification($inputGet)
{
	$spesification = new PicoSpecification();

	if($inputGet->getAlbumId() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('albumId', $inputGet->getAlbumId());
		$spesification->addAnd($predicate1);
	}

	if($inputGet->getName() != "" || $inputGet->getTitle() != "")
	{
		$spesificationTitle = new PicoSpecification();
		
		if($inputGet->getName() != "")
		{
			$predicate1 = new PicoPredicate();
			$predicate1->like('name', PicoPredicate::generateCenterLike($inputGet->getName()));
			$spesificationTitle->addOr($predicate1);
			
			$predicate2 = new PicoPredicate();
			$predicate2->like('title', PicoPredicate::generateCenterLike($inputGet->getName()));
			$spesificationTitle->addOr($predicate2);
		}
		if($inputGet->getTitle() != "")
		{
			$predicate3 = new PicoPredicate();
			$predicate3->like('name', PicoPredicate::generateCenterLike($inputGet->getTitle()));
			$spesificationTitle->addOr($predicate3);
			
			$predicate4 = new PicoPredicate();
			$predicate4->like('title', PicoPredicate::generateCenterLike($inputGet->getTitle()));
			$spesificationTitle->addOr($predicate4);
		}
		
		$spesification->addAnd($spesificationTitle);
	}
	
	
	if($inputGet->getProducerId() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('producerId', $inputGet->getProducerId());
		$spesification->addAnd($predicate1);
	}
	
	return $spesification;
}

$album = new Album(null, $database);
$rowData = array();
try
{
	$album->findOneByAlbumId($inputGet->getAlbumId());

	$sortable = new PicoSortable();
	$sort2 = new PicoSort('albumId', PicoSortable::ORDER_TYPE_ASC);
	$sortable->addSortable($sort2);

	$spesification = createAlbumSpecification(new InputGet());

	$pageData = $player->findAll($spesification, null, $sortable, true);
	$rowData = $pageData->getResult();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}

if(!empty($rowData))
{
	foreach($rowData $album)
	{
		// do something here
		// $album is instanceof Album class
		// You can use all its features
	}
}
```

**Example 3**

Song specification from `$_GET`

```php
/**
 * Create Song specification
 * @param PicoRequestBase $inputGet
 * $@param array|null $additional
 * @return PicoSpecification
 */
public static function createSongSpecification($inputGet, $additional = null) //NOSONAR
{
	$spesification = new PicoSpecification();

	if($inputGet->getSongId() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('songId', $inputGet->getSongId());
		$spesification->addAnd($predicate1);
	}

	if($inputGet->getGenreId() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('genreId', $inputGet->getGenreId());
		$spesification->addAnd($predicate1);
	}

	if($inputGet->getAlbumId() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('albumId', $inputGet->getAlbumId());
		$spesification->addAnd($predicate1);
	}

	if($inputGet->getProducerId() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('producerId', $inputGet->getProducerId());
		$spesification->addAnd($predicate1);
	}

	if($inputGet->getName() != "" || $inputGet->getTitle() != "")
	{
		$spesificationTitle = new PicoSpecification();
		
		if($inputGet->getName() != "")
		{
			$predicate1 = new PicoPredicate();
			$predicate1->like('name', PicoPredicate::generateCenterLike($inputGet->getName()));
			$spesificationTitle->addOr($predicate1);
			
			$predicate2 = new PicoPredicate();
			$predicate2->like('title', PicoPredicate::generateCenterLike($inputGet->getName()));
			$spesificationTitle->addOr($predicate2);
		}
		if($inputGet->getTitle() != "")
		{
			$predicate3 = new PicoPredicate();
			$predicate3->like('name', PicoPredicate::generateCenterLike($inputGet->getTitle()));
			$spesificationTitle->addOr($predicate3);
			
			$predicate4 = new PicoPredicate();
			$predicate4->like('title', PicoPredicate::generateCenterLike($inputGet->getTitle()));
			$spesificationTitle->addOr($predicate4);
		}
		
		$spesification->addAnd($spesificationTitle);
	}

	if($inputGet->getSubtitle() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->like('subtitle', PicoPredicate::generateCenterLike($inputGet->getSubtitle()));
		$spesification->addAnd($predicate1);
	}

	if($inputGet->getVocalist() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('artistVocalist', $inputGet->getVocalist());
		$spesification->addAnd($predicate1);
	}

	if($inputGet->getSubtitleComplete() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('subtitleComplete', $inputGet->getSubtitleComplete());
		$spesification->addAnd($predicate1);
	}

	if($inputGet->getVocal() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('vocal', $inputGet->getVocal());
		$spesification->addAnd($predicate1);
	}

	if($inputGet->getActive() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('active', $inputGet->getActive());
		$spesification->addAnd($predicate1);
	}

	if(isset($additional) && is_array($additional))
	{
		foreach($additional as $key=>$value)
		{
			$predicate2 = new PicoPredicate();          
			$predicate2->equals($key, $value);
			$spesification->addAnd($predicate2);
		}
	}
	
	return $spesification;
}


$orderMap = array(
    'name'=>'name', 
    'title'=>'title', 
    'rating'=>'rating',
    'albumId'=>'albumId', 
    'album'=>'albumId', 
    'trackNumber'=>'trackNumber',
    'genreId'=>'genreId', 
    'genre'=>'genreId',
    'producerId'=>'producerId',
    'artistVocalId'=>'artistVocalId',
    'artistVocalist'=>'artistVocalId',
    'artistComposer'=>'artistComposer',
    'artistArranger'=>'artistArranger',
    'duration'=>'duration',
    'subtitleComplete'=>'subtitleComplete',
    'vocal'=>'vocal',
    'active'=>'active'
);
$defaultOrderBy = 'albumId';
$defaultOrderType = 'desc';
$pagination = new PicoPagination($cfg->getResultPerPage());

$spesification = SpecificationUtil::createSongSpecification($inputGet);

if($pagination->getOrderBy() == '')
{
	$sortable = new PicoSortable();
	$sort1 = new PicoSort('albumId', PicoSortable::ORDER_TYPE_DESC);
	$sortable->addSortable($sort1);
	$sort2 = new PicoSort('trackNumber', PicoSortable::ORDER_TYPE_ASC);
	$sortable->addSortable($sort2);
}
else
{
	$sortable = new PicoSortable($pagination->getOrderBy($orderMap, $defaultOrderBy), $pagination->getOrderType($defaultOrderType));
}

$pagable = new PicoPagable(new PicoPage($pagination->getCurrentPage(), $pagination->getPageSize()), $sortable);

$songEntity = new Song(null, $database);
$pageData = $songEntity->findAll($spesification, $pagable, $sortable, true);

$rowData = $pageData->getResult();

if(!empty($rowData))
{
	foreach($rowData $song)
	{
		// do something here
		// $song is instanceof Song class
		// You can use all its features
	}
}
	
```