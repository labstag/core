<?php

namespace Labstag\Service;

use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

class VichImageFieldService
{
    public function __construct(
        protected PropertyMappingFactory $propertyMappingFactory
    )
    {
        
    }

    public function getBasePath($entity, $type)
    {
        $object  = $this->propertyMappingFactory->fromField(new $entity(), $type);

        return $object->getUriPrefix();
    }
}
