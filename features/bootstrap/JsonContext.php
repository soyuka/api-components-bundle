<?php

/*
 * This file is part of the Silverback API Component Bundle Project
 *
 * (c) Daniel West <daniel@silverback.is>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Silverback\ApiComponentBundle\Features\Bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Context\JsonContext as BehatchJsonContext;
use Behatch\Json\Json;
use Behatch\Json\JsonInspector;
use Behatch\Json\JsonSchema;
use PHPUnit\Framework\Assert;

class JsonContext implements Context
{
    private JsonInspector $inspector;
    private ?BehatchJsonContext $jsonContext;

    public function __construct()
    {
        $this->inspector = new JsonInspector('javascript');
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $this->jsonContext = $scope->getEnvironment()->getContext(BehatchJsonContext::class);
    }

    /**
     * @Then /^the JSON should be deep equal to:$/
     */
    public function theJsonShouldBeDeepEqualTo(PyStringNode $content): void
    {
        $actual = $this->getJson();
        try {
            $expected = new Json($content);
        } catch (\Exception $e) {
            throw new \Exception('The expected JSON is not a valid');
        }

        $actual = new Json(json_encode($this->sortArrays($actual->getContent())));
        $expected = new Json(json_encode($this->sortArrays($expected->getContent())));

        $this->jsonContext->assertSame(
            $expected->getContent(),
            $actual->getContent(),
            "The json is equal to:\n" . $actual->encode()
        );
    }

    /**
     * @Then /^the JSON should be a superset of:$/
     */
    public function theJsonIsASupersetOf(PyStringNode $content): void
    {
        $actual = json_decode($this->getContent(), true);
        Assert::assertArraySubset(json_decode($content->getRaw(), true), $actual);
    }

    private function sortArrays($obj)
    {
        $isObject = \is_object($obj);

        foreach ($obj as $key => $value) {
            if (null === $value || is_scalar($value)) {
                continue;
            }

            if (\is_array($value)) {
                sort($value);
            }

            $value = $this->sortArrays($value);

            $isObject ? $obj->{$key} = $value : $obj[$key] = $value;
        }

        return $obj;
    }

    /**
     * @Then the JSON should be valid according to the schema file :file
     */
    public function theJsonShouldBeValidAccordingToTheSchemaFile(string $file): void
    {
        try {
            $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema(new PyStringNode([file_get_contents(__DIR__ . '/../schema/' . $file)], 1));
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage() . "\n\nThe json is equal to:\n" . $this->getJson()->encode());
        }
    }

    /**
     * @Then the JSON should be an array with each entry valid according to the schema file :file
     */
    public function theJsonShouldBeAnArrayWithEachEntryValidAccordingToTheSchemaFile(string $file): void
    {
        $json = $this->getJson();
        $schema = new PyStringNode([file_get_contents(__DIR__ . '/../schema/' . $file)], 1);
        try {
            foreach ($json as $item) {
                $this->inspector->validate(
                    $item,
                    new JsonSchema($schema)
                );
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage() . "\n\nThe json is equal to:\n" . $this->getJson()->encode());
        }
    }

    private function getJson()
    {
        return new Json($this->getContent());
    }

    private function getContent(): string
    {
        return $this->jsonContext->getSession()->getPage()->getContent();
    }
}