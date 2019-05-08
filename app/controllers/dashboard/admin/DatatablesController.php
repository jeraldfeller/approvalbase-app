<?php

namespace Aiden\Controllers\Admin;

use Aiden\Models\Councils;
use Aiden\Models\Das;
use Aiden\Models\Users;
use Aiden\Models\UsersPhrases;
use Aiden\Controllers\_BaseController;

class DatatablesController extends _BaseController {

    public function phrasesAction() {

        $sqlTypes = $sqlParams = [];
        $totalRecords = count(UsersPhrases::find());

        // Used by sorting logic
        $columns = [
            false, // Checkbox
            "`users`.`name`",
            "`users_phrases`.`phrase`",
            "`users_phrases`.`case_sensitive`",
            "`users_phrases`.`literal_search`",
            "`users_phrases`.`exclude_phrase`",
            "(SELECT COUNT(*) FROM `das_phrases`"
            . " INNER JOIN `das`"
            . " ON `das`.`id` = `das_phrases`.`das_id`"
            . " WHERE 1=1"
            . " AND `das_phrases`.`phrases_id` = `users_phrases`.`id`)"
        ];

        $sql = "SELECT"
                . " `users_phrases`.`id`,"
                . " `users_phrases`.`phrase`,"
                . " `users_phrases`.`case_sensitive`,"
                . " `users_phrases`.`literal_search`,"
                . " `users_phrases`.`exclude_phrase`,"
                . " `users_phrases`.`created`,"
                . " `users`.`name` as userName,"
                . " (SELECT COUNT(*) FROM `das_phrases`"
                . " INNER JOIN `das`"
                . " ON `das`.`id` = `das_phrases`.`das_id`"
                . " WHERE 1=1"
                . " AND `das_phrases`.`phrases_id` = `users_phrases`.`id`) as phraseOccurences"
                . " FROM `users_phrases`"
                . " INNER JOIN `users`"
                . " ON `users`.`id` = `users_phrases`.`users_id`"
                . " WHERE 1=1";

        // Check if we're searching
        $dtSearchArray = $this->request->getQuery("search");
        if (isset($dtSearchArray["value"]) && strlen($dtSearchArray["value"]) > 0) {

            $sql .= " AND `users_phrases`.`phrase` LIKE :search";
            $sqlParams["search"] = "%" . $dtSearchArray["value"] . "%";
            $sqlTypes["search"] = \Phalcon\Db\Column::BIND_PARAM_STR;
        }

        // Columns we're sorting
        $columnsToSort = [];
        $dtColumnArray = $this->request->getQuery("columns");
        $dtOrderArray = $this->request->getQuery("order");
        foreach ($dtOrderArray as $dtOrderColumn) {

            /**
             * Bit of a headache.
             * &order[0][column]=0
             * &order[0][dir]=asc
             */
            $actualColumn = $dtOrderColumn["column"];

            // If $columns[$i] == false, it means we don't want to sort on this column i.e. in the case of checkboxes
            // The "searchable" value is sent from client-side
            if ($dtColumnArray[$actualColumn]["searchable"] === "true" && $columns[$actualColumn] !== false) {
                $columnsToSort[] = [
                    "column_name" => $columns[$actualColumn],
                    "sort_direction" => ($dtOrderColumn["dir"] === "asc" ? "ASC" : "DESC")
                ];
            }
        }
        if (count($columnsToSort) > 0) {

            $sql .= " ORDER BY";
            foreach ($columnsToSort as $columnToSort) {
                $sql .= $columnToSort["column_name"] . " " . $columnToSort["sort_direction"] . ", ";
            }
            $sql = rtrim($sql, ", ");
        }
        else {

            $sql .= ' ORDER BY phraseOccurences DESC';
        }

        // Execute before limit + offset to get totalFIlteredRows
        $phrases = new Das();
        $phrasesRows = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $phrases
                , $phrases->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );
        $totalFilteredRows = count($phrasesRows);

        // Limit
        $sql .= " LIMIT :limit";
        $sqlParams["limit"] = intval($this->request->getQuery("length", "int"));
        $sqlTypes["limit"] = \Phalcon\Db\Column::BIND_PARAM_INT;

        // Offset
        $sql .= " OFFSET :offset";
        $sqlParams["offset"] = intval($this->request->getQuery("start", "int"));
        $sqlTypes["offset"] = \Phalcon\Db\Column::BIND_PARAM_INT;

        // Base model
        $phrases = new UsersPhrases();
        $phrasesRows = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $phrases
                , $phrases->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );

        $data = [];
        foreach ($phrasesRows as $phrase) {

            $checkbox = '<div class="checkbox">'
                    . '<input id="select-checkbox-' . $phrase->getId() . '" type="checkbox" class="dt-checkbox">'
                    . '<label for="select-checkbox-' . $phrase->getId() . '"></label>'
                    . '</div>';

            $dataRow = [
                'DT_RowId' => "phrase_" . $phrase->getId(),
                $checkbox,
                $phrase->userName,
                $phrase->getPhrase(),
                $phrase->getCaseSensitiveCheckboxHtml(true),
                $phrase->getLiteralSearchCheckboxHtml(true),
                $phrase->getExcludePhraseCheckboxHtml(true),
                $phrase->phraseOccurences
            ];

            $data[] = $dataRow;
        }

        // Output
        echo json_encode([
            "draw" => intval($this->request->getQuery("draw", "int")),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFilteredRows,
            "data" => $data
        ]);

    }

    public function usersAction() {

        $columns = array('', 'email', 'level', 'created', 'last_login');
        $sqlParams = [];
        $sqlTypes = [];


        // Check if we're searching
        $dtSearchArray = $this->request->getQuery("search");

        $searchQuery = '';
        if (isset($dtSearchArray["value"]) && strlen($dtSearchArray["value"]) > 0) {
            // wild card query for level
            $a = '';
            $u = '';
            if(stripos('administrator', $dtSearchArray["value"]) !== false){
                $a .= ' OR level = 255 ';
            }
            if(stripos('user', $dtSearchArray["value"]) !== false){
                $u .= ' OR level = 1 ';
            }
            $searchQuery .= ' AND (email LIKE "%' . $dtSearchArray["value"] . '%" '.$u.$a.')';
        }

        $dtColumnArray = $this->request->getQuery("columns");
        $dtOrderArray = $this->request->getQuery("order");

        $sortQuery = $columns[$dtOrderArray[0]['column']] . ' ' . strtoupper($dtOrderArray[0]['dir']);


        $filterLimit = intval($this->request->getQuery("length", "int"));
        $offset = intval($this->request->getQuery("start", "int"));

        $users = new Users();

        // total records
        $sql = 'SELECT count(id) as totalCount FROM users';
        $sqlTotalCount = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            ,  $users
            ,  $users->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );
        $recordsTotal = (int)$sqlTotalCount[0]->totalCount;

        $users = new Users();
        $sql = 'SELECT `id`, `email`, `level`, `created`, `last_login`
                FROM `users`
                WHERE 1=1
                ' . $searchQuery . '
                ORDER BY ' . $sortQuery . '
                LIMIT '.$offset.','.$filterLimit;
        $developmentApplicationRows = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $users
            , $users->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );


        $data = array();
        foreach ($developmentApplicationRows as $row) {
            $checkbox = '<div class="checkbox">'
                . '<input id="select-checkbox-' . $row->getId() . '" type="checkbox" class="dt-checkbox">'
                . '<label for="select-checkbox-' . $row->getId() . '"></label>'
                . '</div>';

            $lastLogin = $row->getLastLogin() ? $row->getLastLogin()->format("d/m/Y") : "";

            $row = [
                'DT_RowId' => "user_" . $row->getId(),
                $checkbox,
                $row->getEmail(),
                $row->getLevelString(),
                $row->getCreated()->format("d/m/Y"),
                $lastLogin
            ];

            $data[] = $row;
        }
        // Output
        echo json_encode([
            "draw" => intval($this->request->getQuery("draw", "int")),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal,
            "data" => $data
        ]);

    }



    public function usersActionCopy() {

        $sqlTypes = $sqlParams = [];
        $totalRecords = count(Users::find());

        // Used by sorting logic
        $columns = [
            false, // Checkbox
            "`users`.`email`",
            "`users`.`level`",
            "`users`.`created`",
            "`users`.`last_login`",
        ];

        $sql = "SELECT `users`.`id`,"
            . " `users`.`email`,"
            . " `users`.`password_hash`,"
            . " `users`.`level`,"
            . " `users`.`created`,"
            . " `users`.`last_login`"
            . " FROM `users`"
            . " WHERE 1=1";

        // Check if we're searching
        $dtSearchArray = $this->request->getQuery("search");
        if (isset($dtSearchArray["value"]) && strlen($dtSearchArray["value"]) > 0) {

            $sql .= " AND `users`.`email` LIKE :search";
            $sqlParams["search"] = "%" . $dtSearchArray["value"] . "%";
            $sqlTypes["search"] = \Phalcon\Db\Column::BIND_PARAM_STR;
        }

        // Columns we're sorting
        $columnsToSort = [];
        $dtColumnArray = $this->request->getQuery("columns");
        $dtOrderArray = $this->request->getQuery("order");
        foreach ($dtOrderArray as $dtOrderColumn) {

            /**
             * Bit of a headache.
             * &order[0][column]=0
             * &order[0][dir]=asc
             */
            $actualColumn = $dtOrderColumn["column"];

            // If $columns[$i] == false, it means we don't want to sort on this column i.e. in the case of checkboxes
            // The "searchable" value is sent from client-side
            if ($dtColumnArray[$actualColumn]["searchable"] === "true" && $columns[$actualColumn] !== false) {
                $columnsToSort[] = [
                    "column_name" => $columns[$actualColumn],
                    "sort_direction" => ($dtOrderColumn["dir"] === "asc" ? "ASC" : "DESC")
                ];
            }
        }
        if (count($columnsToSort) > 0) {

            $sql .= " ORDER BY";
            foreach ($columnsToSort as $columnToSort) {
                $sql .= $columnToSort["column_name"] . " " . $columnToSort["sort_direction"] . ", ";
            }
            $sql = rtrim($sql, ", ");
        }
        else {

            $sql .= ' ORDER BY `users`.`level` DESC, `users`.`id` ASC';
        }

        // Execute before limit + offset to get totalFIlteredRows
        $users = new Das();
        $usersRows = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $users
            , $users->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );
        $totalFilteredRows = count($usersRows);

        // Limit
        $sql .= " LIMIT :limit";
        $sqlParams["limit"] = intval($this->request->getQuery("length", "int"));
        $sqlTypes["limit"] = \Phalcon\Db\Column::BIND_PARAM_INT;

        // Offset
        $sql .= " OFFSET :offset";
        $sqlParams["offset"] = intval($this->request->getQuery("start", "int"));
        $sqlTypes["offset"] = \Phalcon\Db\Column::BIND_PARAM_INT;

        // Base model
        $users = new Users();
        $usersRows = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $users
            , $users->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );

        $data = [];
        foreach ($usersRows as $user) {

            $checkbox = '<div class="checkbox">'
                . '<input id="select-checkbox-' . $user->getId() . '" type="checkbox" class="dt-checkbox">'
                . '<label for="select-checkbox-' . $user->getId() . '"></label>'
                . '</div>';

            $lastLogin = $user->getLastLogin() ? $user->getLastLogin()->format("d-m-Y") : "";

            $row = [
                'DT_RowId' => "user_" . $user->getId(),
                $checkbox,
                $user->getEmail(),
                $user->getLevelString(),
                $user->getCreated()->format("d-m-Y"),
                $lastLogin
            ];

            $data[] = $row;
        }

        // Output
        echo json_encode([
            "draw" => intval($this->request->getQuery("draw", "int")),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFilteredRows,
            "data" => $data
        ]);

    }

    public function leadsAction() {

        $sqlTypes = $sqlParams = [];

        $leadStatus = intval($this->request->getQuery("status"));
        if ($leadStatus === 0) {
            return false;
        }

        // Columns that are used by sorting logic
        $columns = [
            0 => false, // Checkbox
            1 => "`councils`.`name`",
            2 => "`das`.`description`",
            3 => "`das`.`lodge_date`"
        ];

        $totalRecords = count(Das::find());

        // Columns to SELECT
        $sql = "SELECT"
                . " `das`.`id`,"
                . " `das`.`council_id`,"
                . " `das`.`council_reference`,"
                . " `das`.`council_reference_alt`,"
                . " `das`.`council_url`,"
                . " `das`.`description`,"
                . " `das`.`lodge_date`,"
                . " `das`.`estimated_cost`,"
                . " `das`.`status`,"
                . " `das`.`created`";

        // FROM Table
        $sql .= " FROM `das`"
                . " INNER JOIN `councils` ON `das`.`council_id` = `councils`.`id`"
                . " WHERE 1=1"
                . " AND `das`.`status` = :status";

        $sqlParams["status"] = $leadStatus;
        $sqlTypes["status"] = \Phalcon\Db\Column::BIND_PARAM_INT;

        // Check if we're searching
        $dtSearchArray = $this->request->getQuery("search");
        if (isset($dtSearchArray["value"]) && strlen($dtSearchArray["value"]) > 0) {

            $filtering = true;

            $sql .= " AND `das`.`description` LIKE :search";
            $sqlParams["search"] = "%" . $dtSearchArray["value"] . "%";
            $sqlTypes["search"] = \Phalcon\Db\Column::BIND_PARAM_STR;
        }

        // Check if we're filtering seen (All, Read, Unread)
        $tableFilter = $this->request->getQuery("customSearch")['councils'];
        if (count($tableFilter) > 0) {
            $councilQuery = array();
            for($x = 0; $x < count($tableFilter); $x++){
                $council = Councils::findFirstById($tableFilter[$x]);
                if ($council) {
                    $filtering = true;
                    $councilQuery[] = '`das`.`council_id` = '.$council->getId();
                }
            }
            $sql .= ' AND ('.implode(' OR ', $councilQuery).')';
        }



        // Columns we're sorting
        $columnsToSort = [];
        $dtColumnArray = $this->request->getQuery("columns");
        $dtOrderArray = $this->request->getQuery("order");
        foreach ($dtOrderArray as $dtOrderColumn) {

            /**
             * Bit of a headache.
             * &order[0][column]=0
             * &order[0][dir]=asc
             */
            $actualColumn = $dtOrderColumn["column"];

            // If $columns[$i] == false, it means we don't want to sort on this column i.e. in the case of checkboxes
            // The "searchable" value is sent from client-side
            if ($dtColumnArray[$actualColumn]["searchable"] === "true" && $columns[$actualColumn] !== false) {
                $columnsToSort[] = [
                    "column_name" => $columns[$actualColumn],
                    "sort_direction" => ($dtOrderColumn["dir"] === "asc" ? "ASC" : "DESC")
                ];
            }
        }
        if (count($columnsToSort) > 0) {

            $sql .= " ORDER BY";
            foreach ($columnsToSort as $columnToSort) {
                $sql .= $columnToSort["column_name"] . " " . $columnToSort["sort_direction"] . ", ";
            }
            $sql = rtrim($sql, ", ");
        }
        else {

            $sql .= ' ORDER BY `das`.`lodge_date` DESC';
        }

        // Execute before limit + offset to get totalFIlteredRows
        $das = new Das();
        $dasRows = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $das
                , $das->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );


        $totalFilteredRows = count($dasRows);

        // Limit
        $sql .= " LIMIT :limit";
        $sqlParams["limit"] = intval($this->request->getQuery("length", "int"));
        $sqlTypes["limit"] = \Phalcon\Db\Column::BIND_PARAM_INT;

        // Offset
        $sql .= " OFFSET :offset";
        $sqlParams["offset"] = intval($this->request->getQuery("start", "int"));
        $sqlTypes["offset"] = \Phalcon\Db\Column::BIND_PARAM_INT;

        // Rows to be displayed
        $das = new Das();
        $dasRows = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $das
                , $das->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );

        $data = [];
        foreach ($dasRows as $dasRow) {

            $checkbox = '<div class="checkbox">'
                    . '<input id="select-checkbox-' . $dasRow->getId() . '" type="checkbox" class="dt-checkbox">'
                    . '<label for="select-checkbox-' . $dasRow->getId() . '"></label>'
                    . '</div>';

            $dataRow = [
                'DT_RowId' => "lead_" . $dasRow->getId(),
                'DT_RowClass' => $dasRow->getId(),
                'DT_RowClass' => ($dasRow->getId() == $this->request->getQuery("currentViewedLead", "int") ? 'bg-secondary text-white' : ''),
                $checkbox,
                $dasRow->Council->getName(),
                $dasRow->getDescription(),
                $dasRow->getLodgeDate() ? $dasRow->getLodgeDate()->format("d/m/Y") : "Unknown"
            ];

            $data[] = $dataRow;
        }

        // Output
        echo json_encode([
            "draw" => intval($this->request->getQuery("draw", "int")),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFilteredRows,
            "data" => $data
        ]);

    }

    public function councilsAction() {

        $sqlTypes = $sqlParams = [];

        // Columns that are used by sorting logic
        $columns = [
            0 => false, // Checkbox
            1 => "`councils`.`name`",
            2 => "`councils`.`last_scrape`",
            3 => "(SELECT `das`.`created`"
            . " FROM `das`"
            . " WHERE `das`.`council_id` = `councils`.`id`"
            . " ORDER BY `das`.`created` DESC"
            . " LIMIT 1)"
        ];

        // Columns to SELECT
        $sql = "SELECT"
                . " `councils`.`id`,"
                . " `councils`.`name`,"
                . " `councils`.`website_url`,"
                . " `councils`.`logo_url`,"
                . " `councils`.`last_scrape`,"
                . " (SELECT `das`.`created`"
                . " FROM `das`"
                . " WHERE `das`.`council_id` = `councils`.`id`"
                . " ORDER BY `das`.`created` DESC"
                . " LIMIT 1) as last_lead"
                . " FROM `councils`"
                . " WHERE 1=1";

        // Check if we're searching
        $dtSearchArray = $this->request->getQuery("search");

        if (isset($dtSearchArray["value"]) && strlen($dtSearchArray["value"]) > 0) {

            $filtering = true;

            $sql .= " AND `councils`.`name` LIKE :search";
            $sqlParams["search"] = "%" . $dtSearchArray["value"] . "%";
            $sqlTypes["search"] = \Phalcon\Db\Column::BIND_PARAM_STR;
        }

        // Columns we're sorting
        $columnsToSort = [];
        $dtColumnArray = $this->request->getQuery("columns");
        $dtOrderArray = $this->request->getQuery("order");
        foreach ($dtOrderArray as $dtOrderColumn) {

            /**
             * Bit of a headache.
             * &order[0][column]=0
             * &order[0][dir]=asc
             */
            $actualColumn = $dtOrderColumn["column"];

            // If $columns[$i] == false, it means we don't want to sort on this column i.e. in the case of checkboxes
            // The "searchable" value is sent from client-side
            if ($dtColumnArray[$actualColumn]["searchable"] === "true" && $columns[$actualColumn] !== false) {
                $columnsToSort[] = [
                    "column_name" => $columns[$actualColumn],
                    "sort_direction" => ($dtOrderColumn["dir"] === "asc" ? "ASC" : "DESC")
                ];
            }
        }
        if (count($columnsToSort) > 0) {

            $sql .= " ORDER BY";
            foreach ($columnsToSort as $columnToSort) {
                $sql .= $columnToSort["column_name"] . " " . $columnToSort["sort_direction"] . ", ";
            }
            $sql = rtrim($sql, ", ");
        }
        else {

            $sql .= ' ORDER BY last_lead DESC';
        }

        // Execute before limit + offset to get totalFIlteredRows
        $councils = new Councils();
        $councilRows = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $councils
                , $councils->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );
        $totalFilteredRows = count($councilRows);

        // Limit
        $sql .= " LIMIT :limit";
        $sqlParams["limit"] = intval($this->request->getQuery("length", "int"));
        $sqlTypes["limit"] = \Phalcon\Db\Column::BIND_PARAM_INT;

        // Offset
        $sql .= " OFFSET :offset";
        $sqlParams["offset"] = intval($this->request->getQuery("start", "int"));
        $sqlTypes["offset"] = \Phalcon\Db\Column::BIND_PARAM_INT;

        // Rows to be displayed
        $councils = new Councils();
        $councilRows = new \Phalcon\Mvc\Model\Resultset\Simple(
                null
                , $councils
                , $councils->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );

        $data = [];
        foreach ($councilRows as $council) {

            // Generate checkbox
            $rowCheckbox = '<div class="checkbox">';
            $rowCheckbox .= sprintf('<input id="select-checkbox-%s" type="checkbox" class="dt-checkbox">', $council->getId());
            $rowCheckbox .= sprintf('<label for="select-checkbox-%s"></label>', $council->getId());
            $rowCheckbox .= '</div>';

            $data[] = [
                'DT_RowId' => sprintf("lead_%s", $council->getId()),
                $rowCheckbox,
                $council->getName(),
                $council->getLastScrapeString(),
                $council->getLastLeadString(),
            ];
        }

        // Output
        echo json_encode([
            "draw" => intval($this->request->getQuery("draw", "int")),
            "recordsTotal" => Councils::find()->count(),
            "recordsFiltered" => count($data),
            "data" => $data
        ]);

    }

}
