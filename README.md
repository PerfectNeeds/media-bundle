Getting Started With PNMediaBundle for manage uploading files like images or documents
==================================

### Prerequisites
1. Symfony 3.4
2. [PNServiceBundle](https://github.com/PerfectNeeds/service-bundle)


Installation
------------

Installation is a quick (I promise!) 9 step process:

1. Download PNMediaBundle using composer
2. Enable the Bundle in AppKernel
3. Create your Image class
4. Create your Document class
5. Create your ImageRepository class
6. Create your DocumentRepository class
7. Configure the PNMediaBundle
8. Import PNMediaBundle routing
9. Update your database schema
------------
### Step 1: Download PNMediaBundle using composer
Require the bundle with composer:
```sh
$ composer require perfectneeds/media-bundle "~1.0"
```
### Step 2: Enable the Bundle in AppKernel
Require the bundle with composer:
```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new \PN\ServiceBundle\PNServiceBundle(),
        new \PN\MediaBundle\PNMediaBundle(),
        // ...
    );
}
```

### Step 3: Create your Image class
The goal of this bundle is to persist some `Image` class to a database. Your first job, then, is to create the
`Image` class for your application. This class can look and act however
you want: add any properties or methods you find useful. This is *your*
`Image` class.

The bundle provides base classes which are already mapped for most
fields to make it easier to create your entity. Here is how you use it:

1.  Extend the base `Image` class (from the `Entity` folder if you are
    using any of the doctrine variants)
2.  Map the `id` field. It must be protected as it is inherited from the
    parent class.

#### Caution!

When you extend from the mapped superclass provided by the bundle, don't redefine the mapping for the other fields as it is provided by the bundle.

In the following sections, you'll see examples of how your `Image` class should look, depending on how you're storing your posts (Doctrine ORM).

##### Note

The doc uses a bundle named `MediaBundle`. However, you can of course place your post class in the bundle you want.

###### Caution!

If you override the __construct() method in your Image class, be sure to call parent::__construct(), as the base Image class depends on this to initialize some fields.


#### Doctrine ORM Image class

If you're persisting your post via the Doctrine ORM, then your `Image` class should live in the Entity namespace of your bundle and look like this to start:

*You can add all relations between other entities in this class

```php
<?php
// src/PN/Bundle/MediaBundle/Entity/Image.php

namespace PN\Bundle\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

// DON'T forget the following use statement!!!
use PN\MediaBundle\Entity\Image as BaseImage;
use PN\MediaBundle\Model\ImageInterface;
use PN\MediaBundle\Model\ImageTrait;

 /**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("image")
 * @ORM\Entity(repositoryClass="PN\MediaBundle\Repository\ImageRepository")
 */
class Image extends BaseImage implements ImageInterface {

    use ImageTrait;
    /**
     * @ORM\PreRemove
     */
    public function preRemove() {
        $this->removeUpload();
    }
    
    // *IMPORTANT* Add this code of you use PNContentBundle 
    /**
     * @ORM\ManyToMany(targetEntity="\PN\Bundle\ContentBundle\Entity\Post", mappedBy="images")
     */
    protected $posts;
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->posts = new \Doctrine\Common\Collections\ArrayCollection();
        
        // your own logic
    }
    
    
    // if not use the PNContentBundle use this constructor
    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```
### Step 4: Create your Document class
The goal of this bundle is to persist some `Document` class to a database. Your first job, then, is to create the
`Document` class for your application. This class can look and act however
you want: add any properties or methods you find useful. This is *your*
`Document` class.

The bundle provides base classes which are already mapped for most
fields to make it easier to create your entity. Here is how you use it:

1.  Extend the base `Document` class (from the `Entity` folder if you are
    using any of the doctrine variants)
2.  Map the `id` field. It must be protected as it is inherited from the
    parent class.

#### Caution!

When you extend from the mapped superclass provided by the bundle, don't redefine the mapping for the other fields as it is provided by the bundle.

In the following sections, you'll see examples of how your `Document` class should look, depending on how you're storing your documents (Doctrine ORM).

##### Note

The doc uses a bundle named `MediaBundle`. However, you can of course place your document class in the bundle you want.

###### Caution!

If you override the __construct() method in your Document class, be sure to call parent::__construct(), as the base Document class depends on this to initialize some fields.


#### Doctrine ORM Document class

If you're persisting your document via the Doctrine ORM, then your `Document` class should live in the Entity namespace of your bundle and look like this to start:

*You can add all relations between other entities in this class

```php
<?php
// src/PN/Bundle/MediaBundle/Entity/Document.php

namespace PN\Bundle\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

// DON'T forget the following use statement!!!
use PN\MediaBundle\Entity\Document as BaseDocument;
use PN\MediaBundle\Model\DocumentInterface;
use PN\MediaBundle\Model\DocumentTrait;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("document")
 * @ORM\Entity(repositoryClass="PN\Bundle\MediaBundle\Repository\DocumentRepository")
 */
class Document extends BaseDocument implements DocumentInterface {

    use DocumentTrait;

    /**
     * @ORM\PreRemove
     */
    public function preRemove() {
        $this->removeUpload();
    }
    
    // if not use the PNContentBundle use this constructor
    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```

### Step 5: Create your ImageRepository class
You can use this `Repository` to add any custom methods 

```php
<?php
// src/PN/Bundle/MediaBundle/Repository/ImageRepository.php


namespace PN\Bundle\MediaBundle\Repository;

use PN\MediaBundle\Repository\ImageRepository as BaseImageRepository;

class ImageRepository extends BaseImageRepository {

}
```


### Step 6: Create your DocumentRepository class
You can use this `Repository` to add any custom methods 

```php
<?php
// src/PN/Bundle/MediaBundle/Repository/DocumentRepository.php


namespace PN\Bundle\MediaBundle\Repository;

use PN\MediaBundle\Repository\DocumentRepository as BaseDocumentRepository;

class DocumentRepository extends BaseDocumentRepository {

}
```

### Step 7: Configure the PNMediaBundle
Add the following configuration to your config.yml file according to which type of datastore you are using.

```ymal
# app/config/config.yml 

doctrine:
   orm:
        # search for the "ResolveTargetEntityListener" class for an article about this
        resolve_target_entities: 
            PN\MediaBundle\Entity\Image: PN\Bundle\MediaBundle\Entity\Image
            PN\MediaBundle\Entity\Document: PN\Bundle\MediaBundle\Entity\Document

pn_media: 
    image: 
        # The fully qualified class name (FQCN) of the Image class which you created in Step 3.
        image_class: PN\Bundle\MediaBundle\Entity\Image
        
        # All supported mime types for images
        mime_types: ['image/gif', 'image/jpeg', 'image/jpg', 'image/png']
        
        # Add here all upload paths for images that not managed by image setting and you'll path this id in upload method
        # *IMPORTANT* this id must be greater than or equal 100
        upload_paths:
            - { id: 100, path: 'banner' }
            - { id: 100, path: 'banner' }
            
    document:
        #The fully qualified class name (FQCN) of the Document class which you created in Step 4.
        document_class: PN\Bundle\MediaBundle\Entity\Document
        
        # All supported mime types for document
        mime_types: ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/mspowerpoint', 'application/powerpoint', 'application/vnd.ms-powerpoint', 'application/x-mspowerpoint', 'application/pdf', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        
        # Add here all upload paths for documents and you'll path this id in upload method
        # *IMPORTANT* this id must be greater than 100
        upload_paths:
            - { id: 100, path: 'application' }
```

### Step 8: Import PNMediaBundle routing files

```ymal
# app/config/routing.yml 

pn_media:
    resource: "@PNMediaBundle/Resources/config/routing.yml"
```

### Step 9: Update your database schema
Now that the bundle is configured, the last thing you need to do is update your database schema because you have added a new entity.

```sh
$ php bin/console doctrine:schema:update --force
```

------
# How to use PNMediaBundle

1. How to upload **image** in Controller
2. How to upload **document** in Controller
--------------------------
#### 1. How to upload **image** in Controller
```php
<?php
$file = $form->get("image")->get("file")->getData();
$this->get('pn_media_upload_image')->uploadSingleImage($entity, $file, 100, $request);

```
* $entity : an instance of your entity that you would like to add this image to it **this entity must be contains one of these methods addImage() or setImage()**
* $file: must be an instance of _FileUploader_
* $type: the type of this entity to set the upload path this type must be found in `ImageSetting` or configured in `app/config.yml`
* $request (optional) : an instance of Symfony\Component\HttpFoundation\Request
* $imageType (Defualt value : Main Image): and any image type 

#### 2. How to upload **document** in Controller
```php
<?php
$file = $form->get("document")->get("file")->getData();
$this->get('pn_media_upload_document')->uploadSingleDocument($entity, $file, 100, $request);

```
* $entity : an instance of your entity that you would like to add this document to it **this entity must be contains one of these methods addDocument() or setDocument()**
* $file: must be an instance of _FileUploader_
* $type: the type of this entity to set the upload path this type must be configured in `app/config.yml`
* $request (optional) : an instance of Symfony\Component\HttpFoundation\Request


Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/PerfectNeeds/media-bundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.