<?php

use PHPUnit\Framework\TestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;

// Include the class to be tested. This is necessary in a non-autoloader environment.
if (file_exists(dirname(__DIR__, 3) . '/administrator/models/supplier.php')) {
    require_once dirname(__DIR__, 3) . '/administrator/models/supplier.php';
}

/**
 * Unit Test for BookingmanagerModelSupplier.
 *
 * NOTE: To properly run these tests, a test helper like `antecedent/patchwork` is required
 * to mock Joomla's static method calls (e.g., Factory::getDbo).
 * The tests are written to be correct *if* such a tool is used to patch the static calls.
 *
 * Example setup with patchwork in a bootstrap file:
 * \Patchwork\replace(Joomla\CMS\Factory::class . '::getDbo', function() { return YourMockDb; });
 * \Patchwork\replace(Joomla\CMS\Factory::class . '::getUser', function() { return YourMockUser; });
 * \Patchwork\replace(Joomla\CMS\Component\ComponentHelper::class . '::getParams', function() { return YourMockParams; });
 */
class SupplierModelTest extends TestCase
{
    /**
     * @var BookingmanagerModelSupplier The model instance to be tested.
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject The mock database driver.
     */
    protected $dbMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject The mock database query object.
     */
    protected $queryMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the database query object to be chainable
        $this->queryMock = $this->createMock(DatabaseQuery::class);
        $this->queryMock->method('select')->willReturn($this->queryMock);
        $this->queryMock->method('from')->willReturn($this->queryMock);
        $this->queryMock->method('where')->willReturn($this->queryMock);
        $this->queryMock->method('order')->willReturn($this->queryMock);
        $this->queryMock->method('insert')->willReturn($this->queryMock);
        $this->queryMock->method('columns')->willReturn($this->queryMock);
        $this->queryMock->method('values')->willReturn($this->queryMock);
        $this->queryMock->method('delete')->willReturn($this->queryMock);
        $this->queryMock->method('join')->willReturn($this->queryMock);
        $this->queryMock->method('clear')->willReturn($this->queryMock);

        // Mock the database driver
        $this->dbMock = $this->createMock(DatabaseDriver::class);
        $this->dbMock->method('getQuery')->willReturn($this->queryMock);
        $this->dbMock->method('getNullDate')->willReturn('0000-00-00 00:00:00');
        $this->dbMock->method('quote')->will($this->returnArgument(0));
        $this->dbMock->method('quoteName')->will($this->returnCallback(fn($name) => "`{$name}`"));
        $this->dbMock->method('escape')->will($this->returnArgument(0));

        // This is where a static mocking tool is essential.
        // We are conceptually patching the Factory to return our mocks.
        Factory::$database = $this->dbMock;
        Factory::$user = $this->createMock(\Joomla\CMS\User\User::class);

        // Create a partial mock of the model to isolate the method being tested.
        // We mock methods that interact with the framework in ways that are hard to test.
        $this->model = $this->getMockBuilder(BookingmanagerModelSupplier::class)
            ->onlyMethods(['getTable', 'getState', 'logChanges', 'parentSave']) // parentSave is a conceptual mock of parent::save
            ->getMock();
    }

    protected function tearDown(): void
    {
        // Clean up static patches if the tool supports it.
        // \Patchwork\undoAll();
        parent::tearDown();
    }

    /**
     * @test
     * @description Tests that the getAllPropertiesWithAssignments method correctly filters by category.
     */
    public function getAllPropertiesWithAssignmentsFiltersByCategory()
    {
        $this->markTestIncomplete('This test requires a static mocking tool like Patchwork to work.');

        // --- Arrange ---

        // 1. Mock the ComponentHelper to return selected categories
        $mockParams = new Registry(['property_categories' => [11, 22]]);
        // \Patchwork\replace(ComponentHelper::class . '::getParams', fn() => $mockParams);

        // 2. Mock the database return for the category lookup
        $this->dbMock->expects($this->at(1)) // The second DB query in the method
            ->method('loadObjectList')
            ->willReturn([(object)['lft' => 10, 'rgt' => 15], (object)['lft' => 20, 'rgt' => 25]]);

        // 3. Mock the database return for the sub-category lookup
        $this->dbMock->expects($this->at(2))
            ->method('loadColumn')
            ->willReturn([11, 12, 22, 23]); // Mocked sub-category IDs

        // --- Assert ---

        // 4. Expect the final query for articles to contain the correct WHERE clause
        $this->queryMock->expects($this->once())
            ->method('where')
            ->with($this->stringContains('a.catid IN (11,12,22,23)'));

        // --- Act ---

        // 5. Call the method
        $this->model->getAllPropertiesWithAssignments(1);
    }

    /**
     * @test
     * @description Tests that the save method correctly updates property assignments.
     */
    public function saveMethodUpdatesPropertyAssignments()
    {
        $this->markTestIncomplete('This test requires a static mocking tool like Patchwork to work.');

        // --- Arrange ---

        // 1. Mock the model's dependencies
        $this->model->method('parentSave')->willReturn(true);
        $this->model->method('getState')->willReturn(123); // Mock the saved supplier ID

        $testData = [
            'id' => 123,
            'name' => 'Test Supplier',
            'properties' => [10, 20] // The new list of assigned properties
        ];

        // --- Assert ---

        // 2. Expect a DELETE query for the old assignments
        $this->queryMock->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('`#__bookingmanager_property_map`'));

        $this->queryMock->expects($this->once())
            ->method('where')
            ->with($this->equalTo('`supplier_id` = 123'));

        // 3. Expect an INSERT query for the new assignments
        $this->queryMock->expects($this->once())
            ->method('insert')
            ->with($this->equalTo('`#__bookingmanager_property_map`'));

        // 4. Expect two `values` calls, one for each new property
        $this->queryMock->expects($this->exactly(2))
            ->method('values')
            ->withConsecutive(
                [$this->equalTo('10,123')],
                [$this->equalTo('20,123')]
            );

        // 5. Expect the database's execute method to be called twice (for DELETE and INSERT)
        $this->dbMock->expects($this->exactly(2))->method('execute');

        // --- Act ---

        // 6. Call the save method
        $this->model->save($testData);
    }
}
