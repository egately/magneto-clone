<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Api;

use Exception;
use LogicException;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessor;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\Reflection\NameFinder;
use Magento\Framework\Reflection\TypeCaster;
use Magento\Framework\Reflection\TypeProcessor;
use ReflectionException;
use ReflectionMethod;
use Zend\Code\Reflection\ClassReflection;

/**
 * DTO processor. Supports both mutable and immutable DTO.
 *
 * @api
 */
class DtoProcessor
{
    /**
     * Strategy for setter hydration
     */
    public const HYDRATOR_STRATEGY_SETTER = 'setter';

    /**
     * Strategy for constructor parameters injection
     */
    public const HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM = 'constructor';

    /**
     * Strategy for constructor data parameter injection
     */
    public const HYDRATOR_STRATEGY_CONSTRUCTOR_DATA = 'data';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @var NameFinder
     */
    private $nameFinder;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var TypeCaster
     */
    private $typeCaster;

    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    /**
     * @var JoinProcessor
     */
    private $joinProcessor;

    /**
     * @param ObjectFactory $objectFactory
     * @param TypeProcessor $typeProcessor
     * @param TypeCaster $typeCaster
     * @param ConfigInterface $config
     * @param NameFinder $nameFinder
     * @param JoinProcessor $joinProcessor
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(
        ObjectFactory $objectFactory,
        TypeProcessor $typeProcessor,
        TypeCaster $typeCaster,
        ConfigInterface $config,
        NameFinder $nameFinder,
        JoinProcessor $joinProcessor,
        ExtensionAttributesFactory $extensionAttributesFactory
    ) {
        $this->config = $config;
        $this->typeProcessor = $typeProcessor;
        $this->nameFinder = $nameFinder;
        $this->objectFactory = $objectFactory;
        $this->typeCaster = $typeCaster;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->joinProcessor = $joinProcessor;
    }

    /**
     * Return true if a class is a data object using "data" constructor field
     *
     * @param string $className
     * @return bool
     */
    private function isDataObject(string $className): bool
    {
        $className = $this->getRealClassName($className);
        return
            is_subclass_of($className, AbstractSimpleObject::class) ||
            is_subclass_of($className, DataObject::class);
    }

    /**
     * Return true if a class is inherited from \Magento\Framework\Model\AbstractModel and requires setDataChange
     *
     * @param string $className
     * @return bool
     */
    private function isDataModel(string $className): bool
    {
        $className = $this->getRealClassName($className);
        return
            is_subclass_of($className, AbstractModel::class);
    }

    /**
     * Get real class name (if preferenced)
     *
     * @param string $className
     * @return string
     */
    private function getRealClassName(string $className): string
    {
        $preferenceClass = $this->config->getPreference($className);
        return $preferenceClass ?: $className;
    }

    /**
     * @param $value
     * @param string $type
     * @return mixed
     */
    private function castType($value, string $type)
    {
        if ($type === 'array' || !$this->typeProcessor->isTypeSimple($type)) {
            return $value;
        }

        return $this->typeCaster->castValueToType($value, $type);
    }

    /**
     * @param $value
     * @param string $type
     * @return array|object
     * @throws ReflectionException
     */
    private function createObjectByType($value, string $type)
    {
        if (is_object($value) || ($type === 'array') || ($type === 'mixed')) {
            return $value;
        }

        if ($this->typeProcessor->isArrayType($type)) {
            $res = [];
            foreach ($value as $k => $subValue) {
                $itemType = $this->typeProcessor->getArrayItemType($type);
                $res[$k] = $this->createObjectByType($subValue, $itemType);
            }

            return $res;
        }

        if ($this->typeProcessor->isTypeSimple($type)) {
            return $this->castType($value, $type);
        }

        return $this->createFromArray($value, $type);
    }

    /**
     * Return the strategy for values injection.
     *
     *
     * @param string $className
     * @param array $data
     * @return array
     * @throws ReflectionException
     */
    public function getValuesHydratingStrategy(string $className, array $data): array
    {
        $strategy = [
            self::HYDRATOR_STRATEGY_SETTER => [],
            self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM => [],
            self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA => [],
        ];

        $className = $this->getRealClassName($className);
        $class = new ClassReflection($className);

        // Enumerate parameters and types
        $paramTypes = [];
        foreach ($data as $propertyName => $propertyValue) {
            $type = $this->getPropertyTypeFromGetterMethod($class, $propertyName);
            $paramTypes[$propertyName] = $type;
        }

        $requiredConstructorParams = [];

        // Check for constructor parameters
        $constructor = $class->getConstructor();
        if ($constructor !== null) {
            // Inject data constructor parameter
            if ($this->isDataObject($class->getName())) {
                foreach ($data as $propertyName => $propertyValue) {
                    $type = $paramTypes[$propertyName];
                    if ($paramTypes[$propertyName] !== '') {
                        $strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA][$propertyName] = [
                            'type' => $type
                        ];
                    }
                }
            }

            // Inject into named parameters if a getter method exists
            $parameters = $constructor->getParameters();
            foreach ($parameters as $parameter) {
                $snakeCaseProperty = SimpleDataObjectConverter::camelCaseToSnakeCase($parameter->getName());
                $type = $paramTypes[$snakeCaseProperty] ?? '';

                if (($type !== '') && isset($data[$snakeCaseProperty])) {
                    unset($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA][$snakeCaseProperty]);
                    $strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM][$snakeCaseProperty] = [
                        'parameter' => $parameter->getName(),
                        'type' => $type
                    ];

                    if (!$parameter->isDefaultValueAvailable()) {
                        $requiredConstructorParams[] = $snakeCaseProperty;
                    }
                }
            }
        }

        // Fall back to setters
        foreach ($data as $propertyName => $propertyValue) {
            $camelCaseProperty = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($propertyName);
            try {
                $setterMethod = $this->nameFinder->getSetterMethodName($class, $camelCaseProperty);
                $type = $paramTypes[$propertyName] ?? '';
                if ($type !== '') {
                    if (in_array($propertyName, $requiredConstructorParams, true)) {
                        continue;
                    }

                    unset(
                        $strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA][$propertyName],
                        $strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM][$propertyName]
                    );

                    $strategy[self::HYDRATOR_STRATEGY_SETTER][$propertyName] = [
                        'type' => $type,
                        'method' => $setterMethod
                    ];
                }

            } catch (LogicException $e) {
                unset($e);
            }
        }

        return $strategy;
    }

    /**
     * Return the property type by its getter name
     * @param ClassReflection $classReflection
     * @param string $propertyName
     * @return string
     */
    private function getPropertyTypeFromGetterMethod(ClassReflection $classReflection, string $propertyName): string
    {
        $camelCaseProperty = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($propertyName);
        try {
            $methodName = $this->nameFinder->getGetterMethodName($classReflection, $camelCaseProperty);
        } catch (Exception $e) {
            return '';
        }

        $methodReflection = $classReflection->getMethod($methodName);
        if ($methodReflection->isPublic()) {
            $paramType = (string) $this->typeProcessor->getGetterReturnType($methodReflection)['type'];
            return $this->typeProcessor->resolveFullyQualifiedClassName($classReflection, $paramType);
        }

        return '';
    }

    /**
     * Populate data object using data in array format.
     *
     * @param array $data
     * @param string $type
     * @param string $interfaceName
     * @return object
     * @throws ReflectionException
     */
    public function createFromArray(array $data, string $type, ?string $interfaceName = null)
    {
        // Normalize snake case properties
        foreach ($data as $k => $v) {
            $snakeCaseKey = SimpleDataObjectConverter::camelCaseToSnakeCase($k);
            if ($snakeCaseKey !== $k) {
                $data[$snakeCaseKey] = $v;
                unset($data[$k]);
            }
        }

        $data = $this->joinProcessor->extractExtensionAttributes($type, $data);

        $interfaceName = $interfaceName ?: $type;
        $strategy = $this->getValuesHydratingStrategy($interfaceName, $data);

        if (isset($data['extension_attributes']) && !is_object($data['extension_attributes'])) {
            $data['extension_attributes'] = $this->extensionAttributesFactory->create(
                $type,
                $data['extension_attributes']
            );
        }

        $constructorParams = [];
        foreach ($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_PARAM] as $paramData => $info) {
            $paramConstructor = $info['parameter'];
            $paramType = $info['type'];
            $constructorParams[$paramConstructor] = $this->createObjectByType($data[$paramData], $paramType);
        }

        if (!empty($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA])) {
            $constructorParams['data'] = [];

            foreach ($strategy[self::HYDRATOR_STRATEGY_CONSTRUCTOR_DATA] as $paramData => $info) {
                $paramType = $info['type'];
                $constructorParams['data'][$paramData] = $this->createObjectByType($data[$paramData], $paramType);
            }
        }

        $resObject = $this->objectFactory->create($type, $constructorParams);

        foreach ($strategy[self::HYDRATOR_STRATEGY_SETTER] as $paramData => $info) {
            $methodName = $info['method'];
            $paramType = $info['type'];
            $resObject->$methodName($this->createObjectByType($data[$paramData], $paramType));
        }

        if ($this->isDataModel($interfaceName)) {
            $resObject->setDataChanges(true);
        }

        return $resObject;
    }

    /**
     * Create a new DTO with updated information from array
     *
     * @param $sourceObject
     * @param array $data
     * @param string|null $objectType
     * @return object
     * @throws ReflectionException
     */
    public function createUpdatedObjectFromArray(
        $sourceObject,
        array $data,
        ?string $objectType = null
    ) {
        $sourceObjectData = $this->getObjectData($sourceObject, $objectType);
        $data = array_replace_recursive($sourceObjectData, $data);

        return $this->createFromArray($data, get_class($sourceObject), $objectType);
    }

    /**
     * @param $value
     * @return mixed
     * @throws ReflectionException
     */
    private function explodeObjectValue($value)
    {
        if (is_object($value)) {
            return $this->getObjectData($value);
        }

        if (is_array($value)) {
            $res = [];
            foreach ($value as $subValue) {
                $res[] = $this->explodeObjectValue($subValue);
            }

            return $res;
        }

        return $value;
    }

    /**
     * Merge data into object data
     *
     * @param $sourceObject
     * @param string|null $objectType
     * @return array
     * @throws ReflectionException
     */
    public function getObjectData($sourceObject, ?string $objectType = null): array
    {
        $objectType = $objectType ?: get_class($sourceObject);
        $sourceObjectMethods = get_class_methods($objectType);

        $res = [];
        foreach ($sourceObjectMethods as $sourceObjectMethod) {
            if (preg_match('/^(is|get)([A-Z]\w*)$/', $sourceObjectMethod, $matches)) {
                $propertyName = SimpleDataObjectConverter::camelCaseToSnakeCase($matches[2]);
                $methodName = $matches[0];

                $methodReflection = new ReflectionMethod($sourceObject, $methodName);
                if ($methodReflection->getNumberOfRequiredParameters() === 0) {
                    $value = $this->explodeObjectValue($sourceObject->$methodName());

                    if (($propertyName === 'extension_attributes' || $propertyName === 'custom_attributes') &&
                        empty($value)
                    ) {
                        continue;
                    }

                    if ($value !== null) {
                        $res[$propertyName] = $this->castType(
                            $value,
                            (string) $methodReflection->getReturnType()
                        );
                    }
                }
            }
        }

        return $res;
    }
}
