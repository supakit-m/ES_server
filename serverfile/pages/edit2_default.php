<?php
session_start();

// ตรวจสอบการเข้าถึง
if (!isset($can_access) || !$can_access) {
    http_response_code(404);
    exit;
}

if (!isset($_SESSION['is_login']) || !$_SESSION['is_login']) {
    header("Location: ?page=login");
    exit;
}

require_once dirname(__FILE__) . "/../database/db.php";

function fetchItems($conn)
{
    $sql = "SELECT i.itemID, i.item, g.defaultItemID 
            FROM items i
            JOIN groups g ON i.groupID = g.groupID
            WHERE i.groupID = 2
            ORDER BY CAST(i.item AS UNSIGNED)";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getDefaultItemID($items)
{
    foreach ($items as $item) {
        if ($item['itemID'] == $item['defaultItemID']) {
            return $item['itemID'];
        }
    }
    return null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    if (!isset($_POST['action'])) {
        echo json_encode([
            "success" => false,
            "msg" => "Invalid 'action' params"
        ]);
        exit();
    }
    switch ($_POST['action']) {
        case 'reload_items':
            $items = fetchItems($conn);
            $defaultItemID = getDefaultItemID($items);
            echo json_encode([
                "success" => true,
                "msg" => "",
                "data" => [
                    "items" => $items,
                    "defaultItemID" => $defaultItemID
                ]
            ]);
            exit();

        case 'delete_item':
            if (isset($_POST['itemID'])) {
                $sql = "DELETE FROM items WHERE itemID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $_POST['itemID']);
                if ($stmt->execute()) {
                    echo json_encode([
                        "success" => true,
                        "msg" => "Item deleted successfully"
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "msg" => "Error deleting item"
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "msg" => "Invalid 'itemID' params"
                ]);
            }
            exit();

        case 'setDefault_item':
            if (isset($_POST['itemID'])) {
                $sql = "UPDATE `groups` SET `defaultItemID`= ? WHERE `groupName` = 'sitLimit'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $_POST['itemID']);
                if ($stmt->execute()) {
                    echo json_encode([
                        "success" => true,
                        "msg" => "Default item set successfully"
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "msg" => "Error setting default item"
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "msg" => "Invalid 'itemID' params"
                ]);
            }
            exit();

        case 'add_item':
            if (isset($_POST['itemValue'])) {
                $groupID = 2; // Replace with the actual group ID
                $sql = "INSERT INTO items (item, groupID) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $_POST['itemValue'], $groupID);
                if ($stmt->execute()) {
                    echo json_encode([
                        "success" => true,
                        "msg" => "Item added successfully"
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "msg" => "Error adding item"
                    ]);
                }
            } else {
                echo json_encode([
                    "success" => false,
                    "msg" => "Invalid 'itemValue' params"
                ]);
            }
            exit();
    }
}

$items = fetchItems($conn);
$defaultItemID = getDefaultItemID($items);


$conn->close(); // Close the database connection


require_once dirname(__FILE__) . "/../template/header.php";
require_once dirname(__FILE__) . "/../template/nav-menu.php";


?>
<style>
    .editbg {
        background-color: #00c8e2;
        color: #000000;
    }
    .active2 {
        background-color: #00c8e2;
        color: #000000;
    }
    .table td .form-check {
        display: flex;
        justify-content: center; /* จัดสวิตช์ให้อยู่ตรงกลางแนวนอน */
        align-items: center; /* จัดสวิตช์ให้อยู่ตรงกลางแนวตั้ง */
        height: 100%; /* ใช้ความสูงของเซลล์ทั้งหมด */
    }
</style>

    <div class="mx-3">
        <div class="row mb-4">
            <div class="col-md-6">
                <label for="edtime2" class="form-label fs-4"><b>ระยะเวลานั่งต่อเนื่องเพื่อแจ้งเตือน :</b></label>
            </div>
            <div class="col-md-2 text-center">
                <input class="form-control text-center fs-4" min="1" type="number" id="addItemInput" value="">
            </div>
            <div class="col-md-1 mt-1">
                <span class="fs-4"><b>นาที</b></span>
            </div>
            <div class="col-md-3 d-flex justify-content-end align-items-end">
                <button class="px-4 btn btn-primary fs-5" onclick="addItem()"><i class="bi bi-plus-lg"></i> เพิ่ม</button>
            </div>
        </div>
        
        <div class="table-responsive w-100">
            <table class="table table-bordered table-hover tb1 w-100">
                <thead class="table">
                    <tr>
                        <th class="text-center bg-dark-subtle fs-4" style="width: 35%;">นาที</th>
                        <th class="text-center bg-dark-subtle fs-4" style="width: 35%;">ค่าเริ่มต้น</th>
                        <th class="text-center bg-dark-subtle fs-4" style="width: 30%;">ลบ</th>
                    </tr>
                </thead>
                <tbody class="fs-5" id="data-content">
                    <!-- Table rows will be dynamically populated here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
    const reloadItems = async () => {
        const res = await fetch('?<?php echo $_SERVER['QUERY_STRING']; ?>', {
            method: 'POST',
            body: new URLSearchParams([
                ["action", "reload_items"],
            ])
        })
        const data = await res.json();
        if (data.success) {
            updateTable(data.data.items, data.data.defaultItemID);
            window.existingItems = data.data.items.map(item => item.item); // เก็บข้อมูลที่มีอยู่ไว้ใน window.existingItems
        }
    };

    const setDefault = async (itemID) => {
        const res = await fetch('?<?php echo $_SERVER['QUERY_STRING']; ?>', {
            method: 'POST',
            body: new URLSearchParams([
                ["action", "setDefault_item"],
                ["itemID", itemID]
            ])
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({
                icon: "success",
                // title: data.msg,
                title: "ตั้งเป็นค่าเริ่มต้นเรียบร้อย",
                timer: 1000,
                showConfirmButton: false,
            }).then(() => {
                reloadItems();
            });
        } else {
            Swal.fire({
                icon: "error",
                title: data.msg,
            });
        }
    };

    const deleteItem = async (itemID) => {
        const res = await fetch('?<?php echo $_SERVER['QUERY_STRING']; ?>', {
            method: 'POST',
            body: new URLSearchParams([
                ["action", "delete_item"],
                ["itemID", itemID]
            ])
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({
                icon: "success",
                title: "ลบข้อมูลเรียบร้อย",
                timer: 1000,
                showConfirmButton: false,
            }).then(() => {
                reloadItems();
            });
        } else {
            Swal.fire({
                icon: "error",
                title: data.msg,
            });
        }
    }

    const addItem = async () => {
        const input = document.getElementById("addItemInput");
        const inputValue = input.value.trim();

        if (input.value === "" || input.value <= 0) {
            Swal.fire({
                icon: "error",
                title: "กรุณากรอกข้อมูลที่มากว่า 0"
            });
            return;
        }
        if (checkDuplicate(inputValue)) {
            Swal.fire({
                icon: "error",
                title: "มีข้อมูลนี้อยู่แล้ว"
            });
            return;
        }

        const res = await fetch('?<?php echo $_SERVER['QUERY_STRING']; ?>', {
            method: 'POST',
            body: new URLSearchParams([
                ["action", "add_item"],
                ["itemValue", input.value]
            ])
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({
                icon: "success",
                title: "เพิ่มข้อมูลเรียบร้อย",
                timer: 1000,
                showConfirmButton: false,
            }).then(() => {
                reloadItems();
            });
        } else {
            Swal.fire({
                icon: "error",
                title: data.msg,
            });
        }
        input.value = "";
    }

    const checkDuplicate = (value) => {
        return window.existingItems && window.existingItems.includes(value);
    };

    const updateTable = (items, defaultItemID) => {
        const tbody = document.getElementById("data-content");
        tbody.innerHTML = items.map(item => {
            return `<tr>
                    <td class='text-center'>${item.item}</td>
                    <td class='text-center'>
                        <label class="form-check">
                            <input class="form-check-input fs-3 border-dark" type="radio" name="defalut" ${item.itemID === defaultItemID ? 'checked' : ''} onchange="setDefault(${item.itemID})">
                        </label>
                    </td>
                    <td class='text-center'><button class="btn btn-danger fs-5" ${item.itemID === defaultItemID ? 'disabled' : ''} onclick="deleteItem(${item.itemID})"><i class="bi bi-trash3"></i> ลบ</button></td>
                </tr>`;
        }).join('');
    };

    document.addEventListener('DOMContentLoaded', () => {
        reloadItems();
    });
</script>

<?php
require_once dirname(__FILE__) . "/../template/nav-menu-end.php";
require_once dirname(__FILE__) . "/../template/footer.php";
?>