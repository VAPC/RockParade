<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Fixture\EventFixture;
use Tests\FunctionalTester;

/**
 * @author Vehsamrak
 */
class EventControllerTest extends FunctionalTester
{

    const EVENT_NAME_FIRST = 'first event';
    const EVENT_NAME_SECOND = 'first renamed event';
    const EVENT_DATE_FIRST = '2000-08-08 18:18:00';
    const EVENT_DESCRIPTION_FIRST = 'first event description';
    const EVENT_NAME_FIXTURE_FIRST = 'Test Event';
    const EVENT_DATE_FIXTURE_FIRST = '2187-03-03 10:10';

    /** {@inheritDoc} */
    protected function setUp()
    {
        $this->loadFixtures(
            [
                EventFixture::class,
            ]
        );
        parent::setUp();
    }

    /** @test */
    public function createAction_POSTEventEmptyRequest_validationErrors()
    {
        $this->sendPostRequest('/event', []);
        $responseCode = $this->getResponseCode();
        $errors = $this->getResponseContents()['errors'];

        $this->assertEquals(400, $responseCode);
        $this->assertContains('Parameter is mandatory: name.', $errors);
        $this->assertContains('Parameter is mandatory: date (yyyy-MM-dd HH:mm).', $errors);
        $this->assertContains('Parameter is mandatory: description.', $errors);
    }

    /** @test */
    public function createAction_POSTEventWithNameAndDateAndDescriptionRequest_eventCretedAndSavedToDbAndLocationReturned(
    )
    {
        $createEventData = [
            'name'        => self::EVENT_NAME_FIRST,
            'date'        => self::EVENT_DATE_FIRST,
            'description' => self::EVENT_DESCRIPTION_FIRST,
        ];

        $this->sendPostRequest('/event', $createEventData);
        $responseCode = $this->getResponseCode();
        $errors = $this->getResponseContents()['errors'] ?? [];

        $this->assertEquals(201, $responseCode);
        $this->assertEmpty($errors);

        $resourceLocation = $this->getResponseLocation();
        $this->sendGetRequest($resourceLocation);
        $responseCode = $this->getResponseCode();
        $responseData = $this->getResponseContents()['data'];

        $this->assertEquals(200, $responseCode);
        $this->assertEquals($createEventData['name'], $responseData['name']);
        $this->assertEquals($createEventData['date'], $responseData['date']);
        $this->assertEquals($createEventData['description'], $responseData['description']);
    }

    /** @test */
    public function editAction_PUTEventIdEmptyParameters_validationErrors()
    {
        $this->sendPutRequest('/event/1', []);
        $responseCode = $this->getResponseCode();

        $this->assertEquals(400, $responseCode);
    }

    /** @test */
    public function editAction_PUTEventIdNameParameter_eventWithGivenIdChangedNameAndSavedToDb()
    {
        $existingEvent = $this->getFixtureEvent();
        $existingEventId = $existingEvent->getId();

        $parameters = [
            'name'        => self::EVENT_NAME_SECOND,
            'date'        => self::EVENT_DATE_FIRST,
            'description' => self::EVENT_DESCRIPTION_FIRST,
        ];

        $this->sendPutRequest(sprintf('/event/%s', $existingEventId), $parameters);
        $responseCode = $this->getResponseCode();
        $errors = $this->getResponseContents()['errors'] ?? [];

        $this->assertEquals(204, $responseCode);
        $this->assertEmpty($errors);

        $this->sendGetRequest(sprintf('/event/%s', $existingEventId));
        $responseCode = $this->getResponseCode();
        $responseData = $this->getResponseContents()['data'];

        $this->assertEquals(200, $responseCode);
        $this->assertEquals($parameters['name'], $responseData['name']);
        $this->assertEquals($parameters['date'], $responseData['date']);
        $this->assertEquals($parameters['description'], $responseData['description']);
    }
    
    /** @test */
    public function deleteAction_DELETEEventIdRequest_eventDeletedFromDb()
    {
        $existingEvent = $this->getFixtureEvent();
        $existingEventId = $existingEvent->getId();

        $this->sendDeleteRequest(sprintf('/event/%s', $existingEventId));
        $this->assertEquals(204, $this->getResponseCode());

        $this->sendGetRequest(sprintf('/event/%s', $existingEventId));
        $contents = $this->getResponseContents();
        $this->assertContains(sprintf('Event with id "%s" was not found.', $existingEventId), $contents['errors']);
    }

    /**
     * @return \AppBundle\Entity\Event|null|object
     */
    private function getFixtureEvent()
    {
        return $this->getContainer()
                    ->get('rockparade.event_repository')
                    ->findOneByNameAndDate(
                        self::EVENT_NAME_FIXTURE_FIRST,
                        new \DateTime(self::EVENT_DATE_FIXTURE_FIRST)
                    );
    }
}