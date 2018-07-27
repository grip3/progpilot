<?php

/*
 * This file is part of ProgPilot, a static analyzer for security
 *
 * @copyright 2017 Eric Therond. All rights reserved
 * @license MIT See LICENSE at the root of the project for more info
 */


namespace progpilot\Inputs;

use progpilot\Lang;
use progpilot\Utils;
use progpilot\Objects\MyFunction;

class MyInputs
{
    private $customRules;
    private $resolvedIncludes;

    private $sanitizers;
    private $sinks;
    private $sources;
    private $validators;
    private $falsePositives;
    private $excludesFilesAnalysis;
    private $includesFilesAnalysis;
    private $excludesFoldersAnalysis;
    private $includesFoldersAnalysis;

    private $customFile;
    private $resolvedIncludesFile;
    private $falsePositivesFile;
    private $sourcesFile;
    private $sinksFile;
    private $sanitizersFile;
    private $validatorsFile;
    private $excludesFile;
    private $includesFile;

    private $file;
    private $code;
    private $folder;

    public function __construct()
    {
        $this->customRules = [];
        $this->resolvedIncludes = [];
        $this->sanitizers = [];
        $this->sinks = [];
        $this->sources = [];
        $this->validators = [];
        $this->falsePositives = [];
        $this->excludesFilesAnalysis = [];
        $this->includesFilesAnalysis = [];
        $this->excludesFoldersAnalysis = [];
        $this->includesFoldersAnalysis = [];

        $this->customFile = null;
        $this->falsePositivesFile = null;
        $this->resolvedIncludesFile = null;
        $this->sanitizersFile = null;
        $this->sinksFile = null;
        $this->sourcesFile = null;
        $this->validatorsFile = null;
        $this->excludesFile = null;
        $this->includesFile = null;

        $this->file = null;
        $this->code = null;
        $this->folder = null;
    }

    public function getSinksFile()
    {
        return $this->sinksFile;
    }

    public function getSourcesFile()
    {
        return $this->sourcesFile;
    }

    public function getValidatorsFile()
    {
        return $this->validatorsFile;
    }

    public function getSanitizersFile()
    {
        return $this->sanitizersFile;
    }

    public function getIncludedFiles()
    {
        return $this->includesFilesAnalysis;
    }

    public function getIncludedFolders()
    {
        return $this->includesFoldersAnalysis;
    }

    public function getFolder()
    {
        return $this->folder;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function isExcludedFolder($name)
    {
        $name = realpath($name);
        foreach ($this->excludesFoldersAnalysis as $excludeName) {
            if (strpos($name, realpath($excludeName)) === 0) {
                return true;
            }
        }

        return false;
    }

    public function isIncludedFolder($name)
    {
        $name = realpath($name);
        foreach ($this->includesFoldersAnalysis as $includeName) {
            if (strpos($name, realpath($includeName)) === 0) {
                return true;
            }
        }

        return false;
    }

    public function isExcludedFile($name)
    {
        $name = realpath($name);
        foreach ($this->excludesFilesAnalysis as $excludeName) {
            if (realpath($excludeName) === $name) {
                return true;
            }
        }

        return false;
    }

    public function isIncludedFile($name)
    {
        $name = realpath($name);
        foreach ($this->includesFilesAnalysis as $includeName) {
            if (realpath($includeName) === $name) {
                return true;
            }
        }

        return false;
    }

    public function getIncludeByLocation($line, $column, $sourceFile)
    {
        foreach ($this->resolvedIncludes as $myInclude) {
            if ($myInclude->getLine() === $line
                && $myInclude->getColumn() === $column
                    && $myInclude->getSourceFile() === $sourceFile) {
                return $myInclude;
            }
        }

        return null;
    }

    public function getValidatorByName($stackClass, $myFunc, $myClass)
    {
        foreach ($this->validators as $myValidator) {
            if ($myValidator->getName() === $myFunc->getName()) {
                if (!$myValidator->isInstance() && !$myFunc->isType(MyFunction::TYPE_FUNC_METHOD)) {
                    return $myValidator;
                }

                if ($myValidator->isInstance() && $myFunc->isType(MyFunction::TYPE_FUNC_METHOD)) {
                    if (!is_null($myClass) && $myValidator->getInstanceOfName() === $myClass->getName()) {
                        return $myValidator;
                    }

                    $propertiesValidator = explode("->", $myValidator->getInstanceOfName());

                    if (is_array($propertiesValidator)) {
                        $myValidatorInstanceName = $propertiesValidator[0];

                        $myValidatorNumberOfProperties = count($propertiesValidator);
                        $stackNumberOfProperties = count($stackClass);

                        if ($stackNumberOfProperties >= $myValidatorNumberOfProperties) {
                            $knownProperties =
                                $stackClass[$stackNumberOfProperties - $myValidatorNumberOfProperties];

                            foreach ($knownProperties as $propClass) {
                                if ($propClass->getName() === $myValidatorInstanceName) {
                                    return $myValidator;
                                }
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    public function getSanitizerByName($stackClass, $myFunc, $myClass)
    {
        foreach ($this->sanitizers as $mySanitizer) {
            if ($mySanitizer->getName() === $myFunc->getName()) {
                if (!$mySanitizer->isInstance() && !$myFunc->isType(MyFunction::TYPE_FUNC_METHOD)) {
                    return $mySanitizer;
                }

                if ($mySanitizer->isInstance() && $myFunc->isType(MyFunction::TYPE_FUNC_METHOD)) {
                    if (!is_null($myClass) && $mySanitizer->getInstanceOfName() === $myClass->getName()) {
                        return $mySanitizer;
                    }

                    $propertiesSanitizer = explode("->", $mySanitizer->getInstanceOfName());

                    if (is_array($propertiesSanitizer)) {
                        $mySanitizerInstanceName = $propertiesSanitizer[0];

                        $mySanitizerNumberOfProperties = count($propertiesSanitizer);
                        $stackNumberOfProperties = count($stackClass);

                        if ($stackNumberOfProperties >= $mySanitizerNumberOfProperties) {
                            $knownProperties =
                            $stackClass[$stackNumberOfProperties - $mySanitizerNumberOfProperties];

                            foreach ($knownProperties as $propClass) {
                                if ($propClass->getName() === $mySanitizerInstanceName) {
                                    return $mySanitizer;
                                }
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    public function getSinkByName($context, $stackClass, $myFunc, $myClass)
    {
        foreach ($this->sinks as $mySink) {
            if ($mySink->getName() === $myFunc->getName()) {
                if (!$mySink->isInstance() && !$myFunc->isType(MyFunction::TYPE_FUNC_METHOD)) {
                    return $mySink;
                }

                if ($mySink->isInstance() && $myFunc->isType(MyFunction::TYPE_FUNC_METHOD)) {
                    if (!is_null($myClass) && $mySink->getInstanceOfName() === $myClass->getName()) {
                        return $mySink;
                    }

                    $propertiesSink = explode("->", $mySink->getInstanceOfName());

                    if (is_array($propertiesSink)) {
                        $mySinkInstanceName = $propertiesSink[0];

                        $mySinkNumberOfProperties = count($propertiesSink);
                        $stackNumberOfProperties = count($stackClass);

                        if ($stackNumberOfProperties >= $mySinkNumberOfProperties) {
                            $knownProperties =
                            $stackClass[$stackNumberOfProperties - $mySinkNumberOfProperties];

                            foreach ($knownProperties as $propClass) {
                                $objectId = $propClass->getObjectId();
                                $myClass = $context->getObjects()->getMyClassFromObject($objectId);
                                
                                if (!is_null($myClass) && $myClass->getName() === $mySinkInstanceName) {
                                    return $mySink;
                                }
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    public function getSourceArrayByName(
        $myFuncOrDef,
        $arrValue = false
    ) {
        foreach ($this->sources as $mySource) {
            if ($mySource->getName() === $myFuncOrDef->getName()
                && $mySource->getIsArray()
                    && $arrValue === false) {
                    return $mySource;
            }
        }

        return null;
    }
    
    public function getSourceByName(
        $stackClass,
        $myFuncOrDef,
        $isFunction = false,
        $instanceName = false,
        $arrValue = false
    ) {
        foreach ($this->sources as $mySource) {
            if ($mySource->getName() === $myFuncOrDef->getName()) {
                $checkFunction = false;
                $checkArray = false;
                $checkInstance = false;

                if (!$instanceName) {
                    $checkInstance = true;
                }


                if ($instanceName && $mySource->isInstance()) {
                    if ($mySource->getInstanceOfName() === $instanceName) {
                        $checkInstance = true;
                    }

                    $propertiesSource = explode("->", $mySource->getInstanceOfName());

                    if (is_array($propertiesSource)) {
                        $mySourceInstanceName = $propertiesSource[0];

                        $mySourceNumberOfProperties = count($propertiesSource);
                        $stackNumberOfProperties = count($stackClass);

                        if ($stackNumberOfProperties >= $mySourceNumberOfProperties) {
                            $knownProperties =
                                $stackClass[$stackNumberOfProperties - $mySourceNumberOfProperties];

                            foreach ($knownProperties as $propClass) {
                                if ($propClass->getName() === $mySourceInstanceName) {
                                    $checkInstance = true;
                                }
                            }
                        }
                    }
                }

                if ($mySource->isFunction() === $isFunction) {
                    $checkFunction = true;
                }

                // if we request an array the source must be an array
                // and array nots equals (like $_GET["p"])
                if (($arrValue !== false && $arrValue !== "PROGPILOT_ALL_INDEX_TAINTED"
                    && $mySource->getIsArray()
                        && is_null($mySource->getArrayValue()))
                            // or we don't request an array
                            // and the source is not an array (echo $hardcoded_tainted)
                            || (!$arrValue && !$mySource->getIsArray())
                            // or we don't request an array
                            // if mysource is a function and a array like that :
                            // $row = mysqli_fetch_assoc()
                            // echo $row[0]
                            // we don't want an array ie : $row = mysqli_fetch_assoc()[0]
                            || (!$arrValue && $mySource->isFunction() && $mySource->getIsArray())) {
                    $checkArray = true;
                }

                // if we request an array the source must be an array and array value equals
                if (($arrValue !== false && $arrValue !== "PROGPILOT_ALL_INDEX_TAINTED"
                    && $mySource->getIsArray()
                        && !is_null($mySource->getArrayValue())
                            && $mySource->getArrayValue() === $arrValue)) {
                    $checkArray = true;
                }

                if ($checkArray && $checkInstance && $checkFunction) {
                    return $mySource;
                }
            }
        }

        return null;
    }

    public function getFalsePositiveById($id)
    {
        foreach ($this->falsePositives as $falsePositive) {
            if ($falsePositive->getId() === $id) {
                return $falsePositive;
            }
        }

        return null;
    }

    public function getSanitizers()
    {
        return $this->sanitizers;
    }

    public function getSinks()
    {
        return $this->sinks;
    }

    public function getSources()
    {
        return $this->sources;
    }

    public function getValidators()
    {
        return $this->validators;
    }

    public function getResolvedIncludes()
    {
        return $this->resolvedIncludes;
    }

    public function getFalsePositives()
    {
        return $this->falsePositivesFile;
    }

    public function getExcludeFiles()
    {
        return $this->excludesFiles;
    }

    public function getIncludeFiles()
    {
        return $this->includesFiles;
    }

    public function getCustomRules()
    {
        return $this->customRules;
    }

    public function setCustomRules($file)
    {
        $this->customFile = $file;
    }

    public function setIncludeFiles($file)
    {
        $this->includesFile = $file;
    }

    public function setExcludeFiles($file)
    {
        $this->excludesFile = $file;
    }

    public function setFalsePositives($file)
    {
        $this->falsePositivesFile = $file;
    }

    public function setResolvedIncludes($file)
    {
        $this->resolvedIncludesFile = $file;
    }

    public function setSources($file)
    {
        $this->sourcesFile = $file;
    }

    public function setSinks($file)
    {
        $this->sinksFile = $file;
    }

    public function setSanitizers($file)
    {
        $this->sanitizersFile = $file;
    }

    public function setValidators($file)
    {
        $this->validatorsFile = $file;
    }

    public function readSanitizers()
    {
        if (is_null($this->sanitizersFile)) {
            $this->sanitizersFile = __DIR__."/../../uptodate_data/php/sanitizers.json";
        }

        if (!is_null($this->sanitizersFile)) {
            if (!file_exists($this->sanitizersFile)) {
                Utils::printError(Lang::FILE_DOESNT_EXIST." (".Utils::encodeCharacters($this->sanitizersFile).")");
            }

            $outputJson = file_get_contents($this->sanitizersFile);

            $parsedJson = json_decode($outputJson);

            if (isset($parsedJson-> {'sanitizers'})) {
                $sanitizers = $parsedJson-> {'sanitizers'};
                foreach ($sanitizers as $sanitizer) {
                    if (!isset($sanitizer-> {'name'})
                                || !isset($sanitizer-> {'language'})) {
                        Utils::printError(Lang::FORMAT_SANITIZERS);
                    }

                    $name = $sanitizer-> {'name'};
                    $language = $sanitizer-> {'language'};

                    $prevent = [];
                    if (isset($sanitizer-> {'prevent'})) {
                        $prevent = $sanitizer-> {'prevent'};
                    }

                    $mySanitizer = new MySanitizer($name, $language, $prevent);

                    if (isset($sanitizer-> {'instanceof'})) {
                        $mySanitizer->setIsInstance(true);
                        $mySanitizer->setInstanceOfName($sanitizer-> {'instanceof'});
                    }

                    if (isset($sanitizer-> {'parameters'})) {
                        $parameters = $sanitizer-> {'parameters'};
                        foreach ($parameters as $parameter) {
                            if (isset($parameter-> {'id'}) && isset($parameter-> {'condition'})) {
                                if (is_int($parameter-> {'id'})
                                            && ($parameter-> {'condition'} === "equals"
                                                    || $parameter-> {'condition'} === "taint"
                                                            || $parameter-> {'condition'} === "sanitize")) {
                                    if ($parameter-> {'condition'} === "equals") {
                                        if (isset($parameter-> {'values'})) {
                                            $mySanitizer->addParameter(
                                                $parameter-> {'id'},
                                                $parameter-> {'condition'},
                                                $parameter-> {'values'}
                                            );
                                        }
                                    } else {
                                        $mySanitizer->addParameter(
                                            $parameter-> {'id'},
                                            $parameter-> {'condition'}
                                        );
                                    }
                                }
                            }
                        }

                        $mySanitizer->setHasParameters(true);
                    }

                    $this->sanitizers[] = $mySanitizer;
                }
            } else {
                Utils::printError(Lang::FORMAT_SANITIZERS);
            }
        }
    }

    public function readSinks()
    {
        if (is_null($this->sinksFile)) {
            $this->sinksFile = __DIR__."/../../uptodate_data/php/sinks.json";
        }

        if (!is_null($this->sinksFile)) {
            if (!file_exists($this->sinksFile)) {
                Utils::printError(Lang::FILE_DOESNT_EXIST." (".Utils::encodeCharacters($this->sinksFile).")");
            }

            $outputJson = file_get_contents($this->sinksFile);
            $parsedJson = json_decode($outputJson);

            if (isset($parsedJson-> {'sinks'})) {
                $sinks = $parsedJson-> {'sinks'};
                foreach ($sinks as $sink) {
                    if (!isset($sink-> {'name'})
                                || !isset($sink-> {'language'})
                                || !isset($sink-> {'attack'})
                                || !isset($sink-> {'cwe'})) {
                        Utils::printError(Lang::FORMAT_SINKS);
                    }

                    $name = $sink-> {'name'};
                    $language = $sink-> {'language'};
                    $attack = $sink-> {'attack'};
                    $cwe = $sink-> {'cwe'};

                    $mySink = new MySink($name, $language, $attack, $cwe);

                    if (isset($sink-> {'instanceof'})) {
                        $mySink->setIsInstance(true);
                        $mySink->setInstanceOfName($sink-> {'instanceof'});
                    }

                    if (isset($sink-> {'condition'})) {
                        $mySink->addGlobalCondition($sink-> {'condition'});
                    }

                    if (isset($sink-> {'parameters'})) {
                        $parameters = $sink-> {'parameters'};
                        foreach ($parameters as $parameter) {
                            if (isset($parameter-> {'id'}) && is_int($parameter-> {'id'})) {
                                if (isset($parameter-> {'condition'})) {
                                    $mySink->addParameter($parameter-> {'id'}, $parameter-> {'condition'});
                                } else {
                                    $mySink->addParameter($parameter-> {'id'});
                                }
                            }
                        }

                        $mySink->setHasParameters(true);
                    }

                    $this->sinks[] = $mySink;
                }
            } else {
                Utils::printError(Lang::FORMAT_SINKS);
            }
        }
    }

    public function readSources()
    {
        if (is_null($this->sourcesFile)) {
            $this->sourcesFile = __DIR__."/../../uptodate_data/php/sources.json";
        }

        if (!is_null($this->sourcesFile)) {
            if (!file_exists($this->sourcesFile)) {
                Utils::printError(Lang::FILE_DOESNT_EXIST." (".Utils::encodeCharacters($this->sourcesFile).")");
            }

            $outputJson = file_get_contents($this->sourcesFile);
            $parsedJson = json_decode($outputJson);

            if (isset($parsedJson-> {'sources'})) {
                $sources = $parsedJson-> {'sources'};
                foreach ($sources as $source) {
                    if (!isset($source-> {'name'})
                                || !isset($source-> {'language'})) {
                        Utils::printError(Lang::FORMAT_SOURCES);
                    }

                    $name = $source-> {'name'};
                    $language = $source-> {'language'};

                    $mySource = new MySource($name, $language);

                    if (isset($source-> {'is_function'}) && $source-> {'is_function'}) {
                        $mySource->setIsFunction(true);
                    }

                    if (isset($source-> {'is_array'}) && $source-> {'is_array'}) {
                        $mySource->setIsArray(true);
                    }

                    if (isset($source-> {'array_index'})) {
                        $arr = array($source-> {'array_index'} => false);
                        $mySource->setArrayValue($arr);
                    }

                    if (isset($source-> {'instanceof'})) {
                        $mySource->setIsInstance(true);
                        $mySource->setInstanceOfName($source-> {'instanceof'});
                    }

                    if (isset($source-> {'return_array_index'})) {
                        $mySource->setReturnArray(true);
                        $mySource->setReturnArrayValue($source-> {'return_array_index'});
                    }

                    if (isset($source-> {'parameters'})) {
                        $parameters = $source-> {'parameters'};
                        foreach ($parameters as $parameter) {
                            if (is_int($parameter-> {'id'})) {
                                $mySource->addParameter($parameter-> {'id'});

                                if (isset($parameter-> {'is_array'})
                                            && $parameter-> {'is_array'}
                                            && isset($parameter-> {'array_index'})) {
                                    $mySource->addConditionParameter(
                                        $parameter-> {'id'},
                                        MySource::CONDITION_ARRAY,
                                        $parameter-> {'array_index'}
                                    );
                                }
                            }
                        }

                        $mySource->setHasParameters(true);
                    }

                    $this->sources[] = $mySource;
                }
            } else {
                Utils::printError(Lang::FORMAT_SOURCES);
            }
        }
    }

    public function readValidators()
    {
        if (is_null($this->validatorsFile)) {
            $this->validatorsFile = __DIR__."/../../uptodate_data/php/validators.json";
        }

        if (!is_null($this->validatorsFile)) {
            if (!file_exists($this->validatorsFile)) {
                Utils::printError(Lang::FILE_DOESNT_EXIST." (".Utils::encodeCharacters($this->validatorsFile).")");
            }

            $outputJson = file_get_contents($this->validatorsFile);
            $parsedJson = json_decode($outputJson);

            if (isset($parsedJson-> {'validators'})) {
                $validators = $parsedJson-> {'validators'};
                foreach ($validators as $validator) {
                    if (!isset($validator-> {'name'})
                                || !isset($validator-> {'language'})) {
                        Utils::printError(Lang::FORMAT_VALIDATORS);
                    }

                    $name = $validator-> {'name'};
                    $language = $validator-> {'language'};

                    $myValidator = new MyValidator($name, $language);

                    if (isset($validator-> {'parameters'})) {
                        $parameters = $validator-> {'parameters'};
                        foreach ($parameters as $parameter) {
                            if (isset($parameter-> {'id'}) && isset($parameter-> {'condition'})) {
                                if (is_int($parameter-> {'id'})
                                            && ($parameter-> {'condition'} === "not_tainted"
                                                    || $parameter-> {'condition'} === "array_not_tainted"
                                                            || $parameter-> {'condition'} === "valid"
                                                                    || $parameter-> {'condition'} === "equals")) {
                                    if ($parameter-> {'condition'} === "equals") {
                                        if (isset($parameter-> {'values'})) {
                                            $myValidator->addParameter(
                                                $parameter-> {'id'},
                                                $parameter-> {'condition'},
                                                $parameter-> {'values'}
                                            );
                                        }
                                    } else {
                                        $myValidator->addParameter(
                                            $parameter-> {'id'},
                                            $parameter-> {'condition'}
                                        );
                                    }
                                }
                            }
                        }

                        $myValidator->setHasParameters(true);
                    }

                    if (isset($validator-> {'instanceof'})) {
                        $myValidator->setIsInstance(true);
                        $myValidator->setInstanceOfName($validator-> {'instanceof'});
                    }

                    $this->validators[] = $myValidator;
                }
            } else {
                Utils::printError(Lang::FORMAT_VALIDATORS);
            }
        }
    }

    public function readResolvedIncludes()
    {
        if (!is_null($this->resolvedIncludesFile)) {
            if (!file_exists($this->resolvedIncludesFile)) {
                Utils::printError(
                    Lang::FILE_DOESNT_EXIST." (".Utils::encodeCharacters($this->resolvedIncludesFile).")"
                );
            }

            $outputJson = file_get_contents($this->resolvedIncludesFile);
            $parsedJson = json_decode($outputJson);

            if (isset($parsedJson-> {'includes'})) {
                $includes = $parsedJson-> {'includes'};
                foreach ($includes as $include) {
                    if (!isset($include-> {'line'})
                                || !isset($include-> {'column'})
                                || !isset($include-> {'source_file'})
                                || !isset($include-> {'value'})) {
                        Utils::printError(Lang::FORMAT_INCLUDES);
                    }

                    if (realpath($include-> {'source_file'})) {
                        $line = $include-> {'line'};
                        $column = $include-> {'column'};
                        $sourceFile = realpath($include-> {'source_file'});
                        $value = $include-> {'value'};

                        $myInclude = new MyInclude($line, $column, $sourceFile, $value);
                        $this->resolvedIncludes[] = $myInclude;
                    }
                }
            } else {
                Utils::printError(Lang::FORMAT_INCLUDES);
            }
        }
    }

    public function readFalsePositives()
    {
        if (!is_null($this->falsePositivesFile)) {
            if (!file_exists($this->falsePositivesFile)) {
                Utils::printError(
                    Lang::FILE_DOESNT_EXIST." (".Utils::encodeCharacters($this->falsePositivesFile).")"
                );
            }

            $outputJson = file_get_contents($this->falsePositivesFile);
            $parsedJson = json_decode($outputJson);

            if (isset($parsedJson-> {'false_positives'})) {
                $falsePositives = $parsedJson-> {'false_positives'};
                foreach ($falsePositives as $falsePositive) {
                    if (!isset($falsePositive-> {'vuln_id'})) {
                        Utils::printError(Lang::FORMAT_FALSE_POSITIVES);
                    }

                    $vulnId = $falsePositive-> {'vuln_id'};

                    $myVuln = new MyVuln($vulnId);
                    $this->falsePositives[] = $myVuln;
                }
            } else {
                Utils::printError(Lang::FORMAT_FALSE_POSITIVES);
            }
        }
    }

    public function readExcludesFile()
    {
        if (!is_null($this->excludesFile)) {
            if (!file_exists($this->excludesFile)) {
                Utils::printError(
                    Lang::FILE_DOESNT_EXIST." (".Utils::encodeCharacters($this->excludesFile).")"
                );
            }

            $outputJson = file_get_contents($this->excludesFile);
            $parsedJson = json_decode($outputJson);

            if (isset($parsedJson-> {'exclude_files'})) {
                $excludeFiles = $parsedJson-> {'exclude_files'};
                foreach ($excludeFiles as $excludeFile) {
                    if (realpath($excludeFile)) {
                        $this->excludesFilesAnalysis[] = realpath($excludeFile);
                    }
                }
            }

            if (isset($parsedJson-> {'exclude_folders'})) {
                $excludeFolders = $parsedJson-> {'exclude_folders'};
                foreach ($excludeFolders as $excludeFolder) {
                    if (realpath($excludeFolder)) {
                        $this->excludesFoldersAnalysis[] = realpath($excludeFolder);
                    }
                }
            }
        }
    }

    public function readIncludesFile()
    {
        if (!is_null($this->includesFile)) {
            if (!file_exists($this->includesFile)) {
                Utils::printError(
                    Lang::FILE_DOESNT_EXIST." (".Utils::encodeCharacters($this->includesFile).")"
                );
            }

            $outputJson = file_get_contents($this->includesFile);
            $parsedJson = json_decode($outputJson);

            if (isset($parsedJson-> {'include_files'})) {
                $includeFiles = $parsedJson-> {'include_files'};
                foreach ($includeFiles as $includeFile) {
                    if (realpath($includeFile)) {
                        $this->includesFilesAnalysis[] = realpath($includeFile);
                    }
                }
            }

            if (isset($parsedJson-> {'include_folders'})) {
                $includeFolders = $parsedJson-> {'include_folders'};
                foreach ($includeFolders as $includeFolder) {
                    if (realpath($includeFolder)) {
                        $this->includesFoldersAnalysis[] = realpath($includeFolder);
                    }
                }
            }
        }
    }

    public function readCustomFile()
    {
        if (is_null($this->customFile)) {
            $this->customFile = __DIR__."/../../uptodate_data/php/rules.json";
        }

        if (!is_null($this->customFile)) {
            if (!file_exists($this->customFile)) {
                Utils::printError(
                    Lang::FILE_DOESNT_EXIST." (".Utils::encodeCharacters($this->customFile).")"
                );
            }

            $outputJson = file_get_contents($this->customFile);
            $parsedJson = json_decode($outputJson);

            if (isset($parsedJson-> {'custom_rules'})) {
                $customRules = $parsedJson-> {'custom_rules'};
                foreach ($customRules as $customRule) {
                    if (isset($customRule-> {'name'})
                                && isset($customRule-> {'description'})
                                && isset($customRule-> {'cwe'})
                                && isset($customRule-> {'attack'})) {
                        $myCustom = new MyCustomRule($customRule-> {'name'}, $customRule-> {'description'});
                        $myCustom->setCwe($customRule-> {'cwe'});
                        $myCustom->setAttack($customRule-> {'attack'});

                        if (isset($customRule-> {'sequence'}) && isset($customRule-> {'action'})) {
                            $myCustom->setType(MyCustomRule::TYPE_SEQUENCE);
                            $myCustom->setAction($customRule-> {'action'});

                            foreach ($customRule-> {'sequence'} as $seq) {
                                if (isset($seq-> {'function_name'}) && isset($seq-> {'language'})) {
                                    $myCustomFunction = null;

                                    if (!isset($seq-> {'action'})) {
                                        $myCustomFunction = $myCustom->addToSequence(
                                            $seq-> {'function_name'},
                                            $seq-> {'language'}
                                        );
                                    } else {
                                        switch ($seq-> {'action'}) {
                                            case 'MUST_VERIFY_DEFINITION':
                                                $myCustomFunction = $myCustom->addToSequenceWithAction(
                                                    $seq-> {'function_name'},
                                                    $seq-> {'language'},
                                                    $seq-> {'action'}
                                                );
                                                break;
                                            default:
                                                $myCustomFunction = $myCustom->addToSequence(
                                                    $seq-> {'function_name'},
                                                    $seq-> {'language'}
                                                );
                                                break;
                                        }
                                    }

                                    if (isset($seq-> {'parameters'}) && !is_null($myCustomFunction)) {
                                        $parameters = $seq-> {'parameters'};
                                        foreach ($parameters as $parameter) {
                                            if (isset($parameter-> {'id'}) && isset($parameter-> {'values'})) {
                                                if (is_int($parameter-> {'id'})) {
                                                    $myCustomFunction->addParameter(
                                                        $parameter-> {'id'},
                                                        $parameter-> {'values'}
                                                    );
                                                }
                                            }
                                        }

                                        $myCustomFunction->setHasParameters(true);
                                    }

                                    if (isset($seq-> {'instanceof'})) {
                                        $myCustomFunction->setIsInstance(true);
                                        $myCustomFunction->setInstanceOfName($seq-> {'instanceof'});
                                    }
                                }
                            }
                        } elseif (isset($customRule-> {'function_name'})
                                     && isset($customRule-> {'language'})
                                     && isset($customRule-> {'action'})) {
                            $myCustom->setType(MyCustomRule::TYPE_FUNCTION);
                            $myCustom->setAction($customRule-> {'action'});
                            $myCustomFunction = $myCustom->addFunctionDefinition(
                                $customRule-> {'function_name'},
                                $customRule-> {'language'}
                            );

                            if (isset($customRule-> {'parameters'})) {
                                $parameters = $customRule-> {'parameters'};
                                foreach ($parameters as $parameter) {
                                    if (isset($parameter-> {'id'}) && isset($parameter-> {'values'})) {
                                        if (is_int($parameter-> {'id'})) {
                                            $myCustomFunction->addParameter(
                                                $parameter-> {'id'},
                                                $parameter-> {'values'}
                                            );
                                        }
                                    }
                                }

                                $myCustomFunction->setHasParameters(true);
                            }

                            if (isset($customRule-> {'instanceof'})) {
                                $myCustomFunction->setIsInstance(true);
                                $myCustomFunction->setInstanceOfName($customRule-> {'instanceof'});
                            }
                        }

                        $this->customRules[] = $myCustom;
                    }
                }
            }
        }
    }
}
