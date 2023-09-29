<?php
// clear all errors
error_reporting(0);
ini_set('display_errors', 0);
// set cors to allow any
header("Access-Control-Allow-Origin: *");
// set json utf-8
header("Content-Type: application/json; charset=UTF-8");

try {

  /**
   * Database credentials
   * For practical purposes, we declare the MySQL database credentials in this part of the code,
   * but we recommend using environment variables for more security.
   *
   */
  $databaseServer = 'db';
  $databaseUsername = 'jeresoft';
  $databasePassword = 'cochiverde';
  $databaseName = 'ascendion';

  // connect to database
  $database = new Database($databaseServer, $databaseUsername, $databasePassword, $databaseName);
  $api = new Api($database);

  $api->router();

  // close db connection
  $database->closeConnection();
} catch (Exception $e) {

  // Error general
  http_response_code(500);
  echo json_encode([
    'error' => "Oops! Something went wrong, but we're on it! 🚀 "
  ]);
}

/**
 * Api class
 *
 * This class is responsible for capturing all HTTP requests,
 * routing them according to their method, and assigning them to their respective resolver.
 *
 */
class Api
{
  /**
   * Store the database instance
   *
   * @var Database
   */
  private Database $db;
  private ?array $queryParams;

  /**
   * Initializes the class with a database instance and the optional
   * query string that may come in API calls.
   *
   * @param Database $database
   */
  public function __construct(Database $database)
  {
    $this->db = $database;
    $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    parse_str($queryString, $this->queryParams);
  }

  /**
   * A method responsible for assigning the required resolver based on the
   * method and query requested in the API
   *
   * @return void
   */
  public function router(): void
  {
    $route = '';
    if (isset($this->queryParams['q'])) {
      $route = $this->queryParams['q'];
    }
    switch ($_SERVER['REQUEST_METHOD']) {
        // read routers
      case 'GET';
        switch ($route) {
          case 'items':
            // The request for a list of items is routed.
            $item = new Item($this->db);
            $where = '';
            if (!empty($this->queryParams['itemType'])) {
              $where = 'itemType = ' . $this->queryParams['itemType'];
            }
            $response = $item->browse($where);
            break;
          case 'request':
            // The request for one request is routed.
            $request = new Request($this->db);
            $response = $request->read($this->queryParams['id']);
            break;
          case 'requests':
            // The request for a list of requests is routed.
            $request = new Request($this->db);
            $response = $request->browse();
            break;
          case 'itemTypes':
            // The request for a list of item types is routed.
            $itemType = new ItemType($this->db);
            $response = $itemType->browse();
            break;
            break;
          default:
            throw new Exception("Query not found! 😵");
        }
        break;
        // write routers
      case 'POST':
        if ($route == 'requests') {
          // Route create or udpate a request
          $request = new Request($this->db);
          if ($_POST['req_id']) {
            // update request resolver
            $response = $request->add($_POST['req_id']);
          } else {
            // create request resolver
            $response = $request->add();
          }
          break;
        }
        break;
        // delete routers
      case 'DELETE':
        if ($route == 'request') {
          // Route to delete a request
          $request = new Request($this->db);
          $response = $request->delete($this->queryParams['id']);
        }
        break;
      default:
        throw new Exception("Method not allowed! 👽");
    }
    http_response_code(200);
    echo json_encode(['data' => $response]);
  }
}

/**
 *  Router class
 *
 *  Resolver is a base class for all API routes;
 *  it has generic attributes and methods that customize data access.
 */
class Resolver
{
  /**
   * database instance
   *
   * @var Database
   */
  protected Database $db;

  /**
   * Data model instace
   *
   * @var Model
   */
  protected Model $model;

  /**
   * The table where the model's information is stored.
   *
   * @var string
   */
  protected string $table = 'default';

  /**
   * Variable that stores the name of the column used as a unique
   * identifier in the database
   *
   * @var string
   */
  protected string $id = 'id';

  /**
   * The constructor of the class that stores the database instance
   * and initializes the data model
   *
   * @param Database $database
   */
  public function __construct(Database $database)
  {
    $this->db = $database;
    $this->model = new Model($database, $this->table, $this->id);
  }

  /**
   * Default method that returns the list of contents from the database table.
   *
   * @return array
   */
  public function browse(): array
  {
    return $this->model->browse();
  }

  /**
   * Default method that returns one contents with the unique identifier
   * from the database table.
   *
   * @param string $id
   * @return array
   */
  public function read(string $id): array
  {
    return $this->model->read((int) $id);
  }
}


/**
 *
 * Item class
 *
 * Item class that extends Resolver, set the table form database
 * and overrides the browse method.
 */
class Item extends Resolver
{
  /**
   * The name of the table where the model stores, reads,
   * or deletes information for an item.
   *
   * @var string
   */
  protected string $table = 'items';

  /**
   * Method responsible for returning a list of items
   * from the database while applying the item type filter.
   *
   * @return array
   */
  public function browse(): array
  {
    $where = isset($_GET['itemType']) ? "where item_type = " . $_GET['itemType'] : '';
    return $this->model->browse($where);
  }
}

/**
 * ItemType class
 *
 * Item type class that extends Resolver, set the table form database
 */
class ItemType extends Resolver
{
  /**
   * The name of the table where the model stores, reads,
   * or deletes information for an item type.
   *
   * @var string
   */
  protected string $table = 'item_types';
}

/**
 * Request class
 *
 * Item class that extends Resolver, set the table form database
 * and overrides the browse method.
 */
class Request extends Resolver
{

  /**
   * The name of the table where the model stores, reads,
   * or deletes information for an request.
   *
   * @var string
   */
  protected string $table = 'requests';

  /**
   * The name of the unique identifier in the database for requests
   *
   * @var string
   */
  protected string $id = 'req_id';

  /**
   * This method is responsible for saving or updating a request in the database.
   *
   * @param string $id
   * @return void
   */
  public function add(string $id = null)
  {
    $items = $_POST['items'];
    $itemType = $_POST['itemType'];
    $items = $this->prepareItems($items, $itemType);
    $values = [
      $_POST['user'],
      date("Y-m-d"),
      date("Y-m-d"),
      $items
    ];
    $types = 'ssss';
    $fields = ['requested_by', 'requested_on',  'ordered_on', 'items'];
    // if the variable 'id' exists, it apply an update; otherwise, it apply a insert.
    if (isset($id)) {
      $this->model->update($fields, $values, $id, 'req_id');
    } else {
      $this->model->insert($fields, $values, $types);
    }
    $requests = $this->browse(false);
    $summary = new Summary($this->db);
    $summary->generate($requests);
    return ['data' => 'ok'];
  }

  /**
   * Undocumented function
   *
   * @param array $items
   * @param string $itemType
   * @return void
   */
  private function prepareItems(array $items, string $itemType)
  {
    $result = [];
    foreach ($items as $item) {
      $result[] = [(int)$item, (int)$itemType];
    }
    return json_encode($result);
  }

  /**
   * This method deletes a request from the database based on its $id.
   *
   * @param string $id
   * @return void
   */
  public function delete(string $id)
  {
    $this->model->delete($id, $this->id);
    $requests = $this->browse(false);
    $summary = new Summary($this->db);
    $summary->generate($requests);
    return ['data' => 'ok'];
  }

  /**
   * This method read a request from the database based on its $id.
   *
   * @param string $id
   * @return array
   */
  public function read(string $id): array
  {
    $request = $this->model->read($id, $this->id);
    $itemTypeCatalog = $this->getItemTypes();
    $itemCatalog = $this->getItems();
    $items = json_decode($request['items']);
    $request['type'] = $itemTypeCatalog[$items[0][1]];
    $request['itemTypeId'] = $items[0][1];
    $request['items'] = $this->convertItems($items, $itemCatalog);
    return $request;
  }

  /**
   * This method returns a list of requests with
   * implicit or explicit items based on the $prepare parameter.
   *
   * @param boolean $prepare
   * @return array
   */
  public function browse(bool $prepare = true): array
  {
    $requests = $this->model->browse();
    if ($prepare) {
      $itemTypeCatalog = $this->getItemTypes();
      $itemCatalog = $this->getItems();
      foreach ($requests as $key => $value) {
        $items = json_decode($value['items']);
        $requests[$key]['type'] = $itemTypeCatalog[$items[0][1]];
        $requests[$key]['itemTypeId'] = $items[0][1];
        $requests[$key]['items'] = $this->convertItems($items, $itemCatalog);
      }
    }
    return $requests;
  }

  /**
   * This method returns a catalog of item types.
   *
   * @return array
   */
  private function getItemTypes(): array
  {
    $itemType = new ItemType($this->db);
    $itemTypes = $itemType->browse();
    return array_reduce($itemTypes, function ($carry, $item) {
      $carry[$item['id']] = $item['name'];
      return $carry;
    }, []);
  }

  /**
   * This method transforms a list of implicit items into explicit ones.
   *
   * @param array $items
   * @param array $itemCatalog
   * @return array
   */
  private function convertItems(array $items, array $itemCatalog): array
  {
    $result = [];
    foreach ($items as $item) {
      $result[] = [
        'id' => $item[0],
        'name' => $itemCatalog[$item[0]]
      ];
    }
    return $result;
  }

  /**
   * This method returns a catalog of items.
   *
   * @return array
   */
  private function getItems(): array
  {
    $item = new Item($this->db);
    $items = $item->browse();
    return array_reduce($items, function ($carry, $item) {
      $carry[$item['id']] = $item['name'];
      return $carry;
    }, []);
  }
}

/**
 * Summary class
 *
 * Class responsible for storing summary data.
 */
class Summary extends Resolver
{

  /**
   * The name of the table where the model stores, reads,
   * or deletes information for an summary.
   *
   * @var string
   */
  protected string $table = 'summary';

  /**
   * The name of the unique identifier in the database for summary
   *
   * @var string
   */
  protected string $id = 'req_id';

  /**
   * Method responsible for generating and storing a summary in the database.
   *
   * @param array $requests
   * @return void
   */
  public function generate(array $requests)
  {
    $summary = [];

    foreach ($requests as $request) {
      $itemsRequest = json_decode($request['items']);
      if (empty($summary[$request['requested_by']]) || empty($summary[$request['requested_by']][$request['ordered_on']])) {
        $items = $this->generateItems($itemsRequest);
      } else {
        $existingItems = json_decode($summary[$request['requested_by']][$request['ordered_on']]['items']);

        $items = $this->generateItems($itemsRequest, $existingItems);
      }
      $summary[$request['requested_by']][$request['ordered_on']]  = [
        'requested_by' => $request['requested_by'],
        'ordered_on' => $request['ordered_on'],
        'items' => json_encode($items)
      ];
    }
    $values = $this->prepareValues($summary);
    $this->model->empty();
    $this->model->multiInsert(['requested_by',  'ordered_on',  'items'], $values, 'sss');
  }

  /**
   * Method that prepares the values of a summary to be saved in the database.
   *
   * @param array $summary
   * @return array
   */
  private function prepareValues(array $summary): array
  {
    $values = [];
    foreach ($summary as $row) {
      foreach ($row as $value) {
        $values[] = [
          $value['requested_by'],
          $value['ordered_on'],
          $value['items']
        ];
      }
    }
    return $values;
  }

  /**
   * Method responsible for generating the structure of items.
   *
   * @param array $itemsRequest
   * @param array $existingItems
   * @return array
   */
  private function generateItems(array $itemsRequest, array $existingItems = []): array
  {
    $itemType = $itemsRequest[0][1];
    $items = [];
    foreach ($itemsRequest as $subarray) {
      $items[] = $subarray[0];
    }
    $index = $this->getIndex($existingItems, $itemType);
    if ($index == -1) {
      $existingItems[] = [$itemType, $items];
    } else {
      $existingItems[$index][1] = array_merge($existingItems[$index][1], $items);
    }
    return $existingItems;
  }

  /**
   * Method to search for the index in an array based on the given value.
   *
   * @param array $array
   * @param string $search
   * @return integer
   */
  private function getIndex(array $array, string $search): int
  {
    $result = -1;
    foreach ($array as $index => $value) {
      if ($value[0] == $search) {
        $result = $index;
      }
    }
    return $result;
  }
}


/**
 * Database class
 *
 * Class responsible for connecting to the MySQL database.
 */
class Database
{
  /**
   * Database connection
   *
   * @var mysqli
   */
  private mysqli $connection;

  /**
   * The Database construct init the database connection
   */
  public function __construct($databaseServer, $databaseUsername, $databasePassword, $databaseNam)
  {
    $this->connection = new mysqli($databaseServer, $databaseUsername, $databasePassword, $databaseNam);

    // check the connection
    if ($this->connection->connect_error) {
      throw new Exception("Oops! The database connection is failed!" . $this->connection->connect_error);
    }
  }

  /**
   * Return the database connection
   *
   * @return mysqli
   */
  public function getConnection(): mysqli
  {
    return $this->connection;
  }

  /**
   * Close the database connection
   *
   * @return void
   */
  public function closeConnection(): void
  {
    $this->connection->close();
  }
}

/**
 * Model class
 *
 * The model class has generic methods to assist with
 * the transactions of create, read, update, and delete
 * operations performed in the database.
 */
class Model
{
  /**
   * database connection
   *
   * @var Database
   */
  private Database $db;

  /**
   * table name
   *
   * @var string
   */
  private string $table;


  /**
   * The constructor of the class that initializes the database connection and the table name.
   *
   * @param Database $database
   * @param string $table
   */
  public function __construct(Database $database, string $table)
  {
    $this->db = $database;
    $this->table = $table;
  }

  /**
   * Method that returns a list of records from the database.
   *
   * @param string $where
   * @return array
   */
  public function browse(string $where = ''): array
  {
    $conn = $this->db->getConnection();
    $sql = "SELECT * FROM {$this->table} {$where}";
    $result = $conn->query($sql);

    // Save the query results in an array
    $data = [];
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $data[] = $row;
      }
    }
    return $data;
  }

  /**
   * Method that returns a single record from the database.
   *
   * @param integer $id
   * @param string $fieldId
   * @return array
   */
  public function read(int $id, string $fieldId = 'id'): array
  {
    $conn = $this->db->getConnection();
    $id = $conn->real_escape_string($id);
    $sql = "SELECT * FROM {$this->table} WHERE {$fieldId} = {$id}";
    $result = $conn->query($sql);

    // Save the query results in an array
    $data = [];
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $data[] = $row;
      }
    }
    $result->close();
    if (empty($data)) {
      return null;
    }
    return $data[0];
  }

  /**
   * Method that deletes a record from the database.
   *
   * @param string $id
   * @param string $fieldId
   * @return void
   */
  public function delete(string $id, string $fieldId = 'id')
  {
    $conn = $this->db->getConnection();
    $id = $conn->real_escape_string($id);
    $sql = "DELETE FROM {$this->table} WHERE {$fieldId} = {$id}";
    $conn->query($sql);
  }

  /**
   * Method to delete all the content from a table in the database.
   *
   * @return void
   */
  public function empty()
  {
    $conn = $this->db->getConnection();
    $sql = "DELETE FROM {$this->table}";
    $conn->query($sql);
  }

  /**
   * Method that inserts a record into the database.
   *
   * @param array $fields
   * @param array $values
   * @param string $types
   * @return void
   */
  public function insert(array $fields, array $values, string $types)
  {
    $conn = $this->db->getConnection();
    // Create a placeholders string for the values
    $placeholders = implode(", ", array_fill(0, count($fields), "?"));

    // Create the dynamic SQL query
    $sql = "INSERT INTO {$this->table} (" . implode(", ", $fields) . ") VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $stmt->close();
  }

  /**
   * Method that performs multiple record inserts into the database.
   *
   * @param array $fields
   * @param array $rows
   * @param string $types
   * @return void
   */
  public function multiInsert(array $fields, array $rows, string $types)
  {
    foreach ($rows as $values) {
      $this->insert($fields, $values, $types);
    }
  }

  /**
   * Method that updates a record in the database.
   *
   * @param array $fields
   * @param array $values
   * @param [type] $id
   * @param string $fieldId
   * @return void
   */
  public function update(array $fields, array $values, $id, $fieldId = 'id')
  {
    $conn = $this->db->getConnection();
    // Escape and quote the values
    $escapedValues = array_map(function ($value) use ($conn) {
      return "'" . $conn->real_escape_string($value) . "'";
    }, $values);

    // Construct the SET clause
    $setClause = "";
    for ($i = 0; $i < count($fields); $i++) {
      $setClause .= $fields[$i] . " = " . $escapedValues[$i];
      if ($i < count($fields) - 1) {
        $setClause .= ", ";
      }
    }
    $id = $conn->real_escape_string($id);
    // Construct the UPDATE query
    $sql = "UPDATE {$this->table} SET $setClause WHERE {$fieldId} = $id";
    $conn->query($sql);
  }
}
