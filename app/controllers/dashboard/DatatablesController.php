<?php

namespace Aiden\Controllers;

use Aiden\Models\Das;
use Aiden\Models\Councils;
use Aiden\Models\DasParties;
use Aiden\Models\DasUsers;
use Aiden\Models\DasUsersSearch;
use Aiden\Models\UsersPhrases;

class DatatablesController extends _BaseController
{

    public function searchAction()
    {
        $columns = array('', 'c.name', 'd.lodge_date', 'd.estimated_cost', 'd.description');



        $specialFilterQuery = ' AND d.description NOT LIKE "%modification%" ';

        $sqlParams = [];
        $sqlTypes = [];

        $customSearchData = $this->request->getQuery("customSearch");
        $clickedDas = (isset($customSearchData['clickedDas']) ? $customSearchData['clickedDas'] : []);
        $startDate = date('Y-m-d', strtotime($customSearchData['startDate']));
        $endDate = date('Y-m-d', strtotime($customSearchData['endDate']));
        $maxCost = $customSearchData['maxCost'];
        $minCost = $customSearchData['minCost'];
        $maxCostValue = $customSearchData['maxCostValue'];
        $includeNull = ($minCost == 0 ? " OR d.estimated_cost IS NULL " : "");
        if ($maxCost == 50000000) {
            $maxCost = 999999999999;
        }
        $costQuery = " AND ((d.estimated_cost >= " . $minCost . " AND d.estimated_cost <= " . $maxCost . ")" . $includeNull . ")";
        $councils = $customSearchData['councils'];


        $councilsQry = '';
        if ($councils != null) {
            $councilsQry = " AND (d.council_id = " . implode(' OR d.council_id = ', $councils) . ") ";
        }

        // checkbox filter
        $caseSensitive = false;
        $literalSearch = $customSearchData['literalSearch'];
        $excludePhrase = $customSearchData['excludePhrase'];
        $metadata = $customSearchData['metadata'];
        $searchAddresses = $customSearchData['searchAddresses'];

        $checkBoxFilterOptions = array(
            'caseSensitive' => $caseSensitive,
            'literalSearch' => $literalSearch,
            'excludePhrase' => $excludePhrase
        );

        $caseSensitiveQuery = ($caseSensitive == 'true' ? ' BINARY ' : '');
        $excludeQuery = ($customSearchData['excludePhrase'] == 'true' ? ' NOT LIKE ' : ' LIKE ');
        if ($literalSearch == 'true') {
            $excludeQuery = ($customSearchData['excludePhrase'] == 'true' ? ' NOT RLIKE ' : ' RLIKE ');
        }


        // metadata
        $metadataQuery = '';
        if ($metadata == 'true') {
            $metadataQuery = ' AND d.estimated_cost > 0 ';
            $costQuery = " AND (d.estimated_cost >= " . $minCost . " AND d.estimated_cost <= " . $maxCost . ")";
        }


        $docQuery = ' HAVING docCount > 0 ';


        // Check if we're searching
        $filter = $customSearchData['filter'];
        $searchFilter = $filter;
        $searchQuery = '';
        $filterByApplicant = '';

        $searchFilterAll = true;
        $filterBy = $customSearchData['filterBy'];

        // address
        $addressQuery = '';
        if($searchAddresses == 'true'){
            $addressQuery .= ' OR d.addresses_arr LIKE "%'.$filter.'%" ';
        }
        if (strlen($filter) > 0) {


            $filter = ($literalSearch == 'true' ? "[[:<:]]" . $filter . "[[:>:]]" : "%" . $filter . "%");
            // filter query by applicant
            $orAnd = ($customSearchData["excludePhrase"] == "true" ? " AND " : " OR ");
            if (count($filterBy) > 0) {
                if (!in_array('applicant', $filterBy) || !in_array('description', $filterBy)) {
                    if (in_array('applicant', $filterBy)) {
                        $filterByApplicant = ' AND p.role = "Applicant" ';
                        $searchFilterAll = false;
                        $searchQuery .= ' AND (p.name ' . $excludeQuery . $caseSensitiveQuery . '"' . $filter . '"'. $addressQuery . ')';
                    }
                    if (in_array('description', $filterBy)) {
                        $searchQuery .= ' AND (d.description ' . $excludeQuery . $caseSensitiveQuery . '"' . $filter . '"'. $addressQuery . ')';
                    }
                } else {
                    $searchQuery .= ' AND (d.description ' . $excludeQuery . $caseSensitiveQuery . '"' . $filter . '"'. $addressQuery . ')';
                }
            } else {

                $searchQuery .= ' AND (d.description ' . $excludeQuery . $caseSensitiveQuery . '"' . $filter . '" '. $addressQuery . ')';
            }



        } else {
            if (count($filterBy) > 0) {
                if (!in_array('applicant', $filterBy) || !in_array('description', $filterBy)) {
                    if (in_array('applicant', $filterBy)) {
                        $filterByApplicant = ' AND p.role = "Applicant" ';
                        $searchFilterAll = false;
                    }
                }
            }
        }
        $dtColumnArray = $this->request->getQuery("columns");
        $dtOrderArray = $this->request->getQuery("order");

        if ($searchFilterAll == true) {

            if ($columns[$dtOrderArray[0]['column']] != 'p.name') {
                $sortQuery = $columns[$dtOrderArray[0]['column']] . ' ' . strtoupper($dtOrderArray[0]['dir']);
            } else {
                $sortQuery = 'd.created' . ' ' . strtoupper($dtOrderArray[0]['dir']);
            }
        } else {
            $sortQuery = $columns[$dtOrderArray[0]['column']] . ' ' . strtoupper($dtOrderArray[0]['dir']);
        }



        $filterLimit = intval($this->request->getQuery("length", "int"));
        $offset = intval($this->request->getQuery("start", "int"));

        // total records
        $totalRecords = count(Das::find());
        $das = new Das();

        if ($searchFilterAll == false) {
            $sql = 'SELECT
                       d.id,
                       d.council_id,
                       d.council_reference,
                       d.council_reference_alt,
                       d.council_url,
                       d.description,
                       d.lodge_date,
                       d.estimated_cost,
                       d.status,
                       d.created,
                       d.crawled,
                       c.name as councilName,
                       c.logo_url,
                       p.name as applicantName,
                       (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) As docCount
                FROM das d, councils c, das_parties p
                WHERE d.council_id = c.id
                AND d.id = p.das_id
                ' . $filterByApplicant . '
                AND ((d.lodge_date >= "' . $startDate . '" AND d.lodge_date <= "' . $endDate . '"))
                ' . $searchQuery . '
                ' . $costQuery . '
                ' . $councilsQry . '
                ' . $metadataQuery . '
    
                ' . $specialFilterQuery . '
                 ' . $docQuery;

        } else {
            $sql = 'SELECT
                       d.id,
                       d.council_id,
                       d.council_reference,
                       d.council_reference_alt,
                       d.council_url,
                       d.description,
                       d.lodge_date,
                       d.estimated_cost,
                       d.status,
                       d.created,
                       d.crawled,
                       c.name as councilName,
                       c.logo_url,
                       (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) As docCount
                FROM das d, councils c
                WHERE d.council_id = c.id
                AND ((d.lodge_date >= "' . $startDate . '" AND d.lodge_date <= "' . $endDate . '") OR (d.created >= "' . $startDate . '" AND d.created <= "' . $endDate . '"))
                ' . $searchQuery . '
                ' . $costQuery . '
                ' . $councilsQry . '
                ' . $metadataQuery . '
               
                ' . $specialFilterQuery . '
                 ' . $docQuery;
        }


        $dasForCount = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );
        $totalFilteredRows = count($dasForCount);
        // Base model
        $das = new Das();
        if ($searchFilterAll == false) {
            $sql = 'SELECT
                       d.id,
                       d.council_id,
                       d.council_reference,
                       d.council_reference_alt,
                       d.council_url,
                       d.description,
                       d.lodge_date,
                       d.estimated_cost,
                       d.status,
                       d.created,
                       d.crawled,
                       c.name as councilName,
                       c.logo_url,
                       p.name as applicantName,
                       (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) As docCount
                FROM das d, councils c, das_parties p
                WHERE d.council_id = c.id
                AND d.id = p.das_id
                ' . $filterByApplicant . '
                AND ((d.lodge_date >= "' . $startDate . '" AND d.lodge_date <= "' . $endDate . '"))
                ' . $searchQuery . '
                ' . $costQuery . '
                ' . $councilsQry . '
                ' . $metadataQuery . '
               
                ' . $specialFilterQuery . '
                 ' . $docQuery . '
                ORDER BY ' . $sortQuery . '
                LIMIT ' . $offset . ',' . $filterLimit;
        } else {
            $sql = 'SELECT
                       d.id,
                       d.council_id,
                       d.council_reference,
                       d.council_reference_alt,
                       d.council_url,
                       d.description,
                       d.lodge_date,
                       d.estimated_cost,
                       d.status,
                       d.created,
                       d.crawled,
                       c.name as councilName,
                       c.logo_url,
                       (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) As docCount
                FROM das d, councils c
                WHERE d.council_id = c.id
                AND ((d.lodge_date >= "' . $startDate . '" AND d.lodge_date <= "' . $endDate . '"))
                ' . $searchQuery . '
                ' . $costQuery . '
                ' . $councilsQry . '
                ' . $metadataQuery . '
               
                ' . $specialFilterQuery . '
                 ' . $docQuery . '
                ORDER BY ' . $sortQuery . '
                LIMIT ' . $offset . ',' . $filterLimit;
        }

        
        // Base model
        $developmentApplicationRows = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );
        $data = array();
        $index = 1;
        foreach ($developmentApplicationRows as $row) {


            // look for saved search from das_users_search
            $savedSearch = DasUsersSearch::getSavedSearch($this->getUser()->getId(), $row->getId());
            if (count($savedSearch) > 0) {
                if ($savedSearch[0]->getStatus() == DasUsersSearch::STATUS_SAVED) {
                    $star = '<i class="star-icon ion-ios7-star font-size-22 starred star_' . $index . '" data-starred="true"></i>';
                } else {
                    $star = '<i class="star-icon ion-ios7-star-outline font-size-22 star_' . $index . '"></i>';
                }
            } else {
                $star = '<span class="star-icon ion-ios7-star-outline font-size-22 star_' . $index . '"></span>';
            }

            $councilImage = ($row->logo_url != null ? $row->logo_url : BASE_URI . 'aiden-assets/images/aiden-anonymous.jpg');
            $council = $row->Council->getName();
            if ($row->getLodgeDate() != null) {
                $currentDateTime = $row->getLodgeDate()->format('c');
                $currentDate = $row->getLodgeDate()->format('d-m-Y');
            } else {
                $currentDateTime = $row->getCreated()->format('c');
                $currentDate = $row->getCreated()->format('d-m-Y');
            }
//            $currentDateTime = $row->getCreated()->format('c');
//            $currentDate = $row->getCreated()->format('d-m-Y');
            $uploaded = '<span class="sub-title">
                                    <i class="ti-timer pdd-right-5"></i>
                                    <span>
                                        <time class="timeago" datetime="' . $currentDateTime . '">' . $currentDate . '</time>
                                    </span>
                                </span>';
            $councilLogo = '<img class="avatar" src="'.$councilImage .'">';

            $dataRow = array(
                "DT_RowId" => $row->getId(),
                "DT_RowClass" => ($row->getId() == $this->request->getQuery("currentViewedLead", "int") ? 'active' : ''). " row_" . $index . " context-menu row-container toggleInfo " . (in_array($row->getId(), $clickedDas) ? 'clicked' : ''),
                $councilLogo,
                $council,
                $uploaded,
                ($row->getEstimatedCost() != 0 ? "$" . number_format($row->getEstimatedCost()) : ''),
                $row->getHighlightedDescription($searchFilter, true, $checkBoxFilterOptions),
                $currentDateTime
            );

            $data[] = $dataRow;
            $index++;
        }


        // Output
        echo json_encode([
            "draw" => intval($this->request->getQuery("draw", "int")),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFilteredRows,
            "data" => $data
        ]);
    }


    public function leadsAction()
    {
        $requestedStatus = $this->request->getQuery("status", "int");
        $requestStatusQry = ($requestedStatus == 2 ? ' AND du.status = 2': '');
        $shoOnAlertsQrt = ($requestedStatus == 1 ? ' AND du.show_on_alerts = 1 ': '');
        $columns = array('', 'c.name', 'd.lodge_date', 'd.estimated_cost', 'd.description');
        $specialFilterQuery = ' AND d.description NOT LIKE "%modification%" ';
        $sqlParams = [];
        $sqlTypes = [];

        $customSearchData = $this->request->getQuery("customSearch");
        $startDate = date('Y-m-d', strtotime($customSearchData['startDate']));
        $endDate = date('Y-m-d', strtotime($customSearchData['endDate']));
        $maxCost = $customSearchData['maxCost'];
        $minCost = $customSearchData['minCost'];
        $maxCostValue = $customSearchData['maxCostValue'];
        $includeNull = ($minCost == 0 ? " OR d.estimated_cost IS NULL " : "");
        if ($maxCost == 50000000) {
            $maxCost = 999999999999;
        }
        $costQuery = " AND ((d.estimated_cost >= " . $minCost . " AND d.estimated_cost <= " . $maxCost . ")" . $includeNull . ")";
        $councils = $customSearchData['councils'];


        $councilsQry = '';
        if ($councils != null) {
            $councilsQry = " AND (d.council_id = " . implode(' OR d.council_id = ', $councils) . ") ";
        }

        // checkbox filter
        $caseSensitive = false;
        $literalSearch = $customSearchData['literalSearch'];
        $excludePhrase = $customSearchData['excludePhrase'];
        $metadata = $customSearchData['metadata'];
        $searchAddresses = $customSearchData['searchAddresses'];

        $checkBoxFilterOptions = array(
            'caseSensitive' => $caseSensitive,
            'literalSearch' => $literalSearch,
            'excludePhrase' => $excludePhrase
        );

        $caseSensitiveQuery = ($caseSensitive == 'true' ? ' BINARY ' : '');
        $excludeQuery = ($customSearchData['excludePhrase'] == 'true' ? ' NOT LIKE ' : ' LIKE ');
        if ($literalSearch == 'true') {
            $excludeQuery = ($customSearchData['excludePhrase'] == 'true' ? ' NOT RLIKE ' : ' RLIKE ');
        }

        // metadata
        $metadataQuery = '';
        if ($metadata == 'true') {
            $metadataQuery = ' AND d.estimated_cost > 0 ';
        }


        $docQuery = ' HAVING docCount > 0 ';

        // Check if we're searching
        $filter = $customSearchData['filter'];
        $searchFilter = $filter;
        $searchQuery = '';
        $filterByApplicant = '';
        $searchFilterAll = true;
        $filterBy = $customSearchData['filterBy'];
        // address
        $addressQuery = '';
        if($searchAddresses == 'true'){
            $addressQuery .= ' OR d.addresses_arr LIKE "%'.$filter.'%" ';
        }
        if (strlen($filter) > 0) {

            $filter = ($literalSearch == 'true' ? "[[:<:]]" . $filter . "[[:>:]]" : "%" . $filter . "%");
            // filter query by applicant
            $orAnd = ($customSearchData["excludePhrase"] == "true" ? " AND " : " OR ");
            if (count($filterBy) > 0) {
                if (!in_array('applicant', $filterBy) || !in_array('description', $filterBy)) {
                    if (in_array('applicant', $filterBy)) {
                        $filterByApplicant = ' AND p.role = "Applicant" ';
                        $searchFilterAll = false;
                        $searchQuery .= ' AND (p.name ' . $excludeQuery . $caseSensitiveQuery . '"' . $filter . '"'. $addressQuery . ')';
                    }
                    if (in_array('description', $filterBy)) {
                        $searchQuery .= ' AND (d.description ' . $excludeQuery . $caseSensitiveQuery . '"' . $filter . '"'. $addressQuery . ')';
                    }
                } else {
                    $searchQuery .= ' AND (d.description ' . $excludeQuery . $caseSensitiveQuery . '"' . $filter . '"'. $addressQuery . ')';
                }
            } else {

                $searchQuery .= ' AND (d.description ' . $excludeQuery . $caseSensitiveQuery . '"' . $filter . '"'. $addressQuery . ')';
            }
        } else {
            if (count($filterBy) > 0) {
                if (!in_array('applicant', $filterBy) || !in_array('description', $filterBy)) {
                    if (in_array('applicant', $filterBy)) {
                        $filterByApplicant = ' AND p.role = "Applicant" ';
                        $searchFilterAll = false;
                    }
                }
            }
        }
        $dtColumnArray = $this->request->getQuery("columns");
        $dtOrderArray = $this->request->getQuery("order");

        if ($searchFilterAll == true) {
            if ($columns[$dtOrderArray[0]['column']] != 'p.name') {
                $sortQuery = $columns[$dtOrderArray[0]['column']] . ' ' . strtoupper($dtOrderArray[0]['dir']);
            } else {
                $sortQuery = 'd.created' . ' ' . strtoupper($dtOrderArray[0]['dir']);
            }
        } else {
            $sortQuery = $columns[$dtOrderArray[0]['column']] . ' ' . strtoupper($dtOrderArray[0]['dir']);
        }


        $filterLimit = intval($this->request->getQuery("length", "int"));
        $offset = intval($this->request->getQuery("start", "int"));

        // total records
        $totalRecords = count(Das::find());
        $das = new Das();

        if ($searchFilterAll == false) {
            $sql = 'SELECT
                       d.id,
                       d.council_id,
                       d.council_reference,
                       d.council_reference_alt,
                       d.council_url,
                       d.description,
                       d.lodge_date,
                       d.estimated_cost,
                       d.status,
                       d.created,
                       d.crawled,
                       du.seen,
                       c.name as councilName,
                       c.logo_url,
                       p.name as applicantName,
                       du.status,
                       (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) As docCount
                FROM das d, councils c, das_parties p, das_users du
                WHERE d.council_id = c.id
                AND d.id = du.das_id
                AND d.id = p.das_id
                AND du.users_id = '.$this->getUser()->getId().'
                ' . $shoOnAlertsQrt . '
                ' . $requestStatusQry . '
                ' . $filterByApplicant . '
                AND ((d.lodge_date >= "' . $startDate . '" AND d.lodge_date <= "' . $endDate . '"))
                ' . $searchQuery . '
                ' . $costQuery . '
                ' . $councilsQry . '
                ' . $metadataQuery . '
              
                ' . $specialFilterQuery . '
            ' . $docQuery;

        } else {
            $sql = 'SELECT
                       d.id,
                       d.council_id,
                       d.council_reference,
                       d.council_reference_alt,
                       d.council_url,
                       d.description,
                       d.lodge_date,
                       d.estimated_cost,
                       d.status,
                       d.created,
                       d.crawled,
                       du.seen,
                       c.name as councilName,
                       c.logo_url,
                       du.status,
                       (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) As docCount
                FROM das d, councils c, das_users du
                WHERE d.council_id = c.id
                AND d.id = du.das_id
                AND du.users_id = '.$this->getUser()->getId().'
                 ' . $shoOnAlertsQrt . '
                ' . $requestStatusQry . '
                AND ((d.lodge_date >= "' . $startDate . '" AND d.lodge_date <= "' . $endDate . '"))
                ' . $searchQuery . '
                ' . $costQuery . '
                ' . $councilsQry . '
                ' . $metadataQuery . '
              
                ' . $specialFilterQuery . '
            ' . $docQuery;
        }

        $dasForCount = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );
        $totalFilteredRows = count($dasForCount);
        // Base model
        $das = new Das();
        if ($searchFilterAll == false) {
            $sql = 'SELECT
                       d.id,
                       d.council_id,
                       d.council_reference,
                       d.council_reference_alt,
                       d.council_url,
                       d.description,
                       d.lodge_date,
                       d.estimated_cost,
                       d.status,
                       d.created,
                       d.crawled,
                       du.seen,
                       c.name as councilName,
                       c.logo_url,
                       p.name as applicantName,
                       du.status,
                       (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) As docCount
                FROM das d, councils c, das_parties p, das_users du
                WHERE d.council_id = c.id
            
                AND d.id = du.das_id
                AND d.id = p.das_id
                AND du.users_id = '.$this->getUser()->getId().'
                 ' . $shoOnAlertsQrt . '
                ' . $requestStatusQry . '
                ' . $filterByApplicant . '
                AND ((d.lodge_date >= "' . $startDate . '" AND d.lodge_date <= "' . $endDate . '"))
                ' . $searchQuery . '
                ' . $costQuery . '
                ' . $councilsQry . '
                ' . $metadataQuery . '
               
                ' . $specialFilterQuery . '
              ' . $docQuery . '
                ORDER BY ' . $sortQuery . '
                LIMIT ' . $offset . ',' . $filterLimit;
        } else {
            $sql = 'SELECT
                       d.id,
                       d.council_id,
                       d.council_reference,
                       d.council_reference_alt,
                       d.council_url,
                       d.description,
                       d.lodge_date,
                       d.estimated_cost,
                       d.status,
                       d.created,
                       d.crawled,
                       du.seen,
                       c.name as councilName,
                       c.logo_url,
                       du.status,
                       (SELECT COUNT(id) FROM das_documents WHERE das_id = d.id) As docCount
                FROM das d, councils c, das_users du
                WHERE d.council_id = c.id
               
                AND d.id = du.das_id
                AND du.users_id = '.$this->getUser()->getId().'
                 ' . $shoOnAlertsQrt . '
                ' . $requestStatusQry . '
                AND ((d.lodge_date >= "' . $startDate . '" AND d.lodge_date <= "' . $endDate . '"))
                ' . $searchQuery . '
                ' . $costQuery . '
                ' . $councilsQry . '
                ' . $metadataQuery . '
                
                ' . $specialFilterQuery . '
              ' . $docQuery . '
                ORDER BY ' . $sortQuery . '
                LIMIT ' . $offset . ',' . $filterLimit;
        }


        // Base model
        $developmentApplicationRows = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );
        $data = array();
        $index = 1;
        foreach ($developmentApplicationRows as $row) {


            if ($row->getStatus() == DasUsers::STATUS_SAVED) {
                $star = '<i class="star-icon ion-ios7-star font-size-22 starred star_' . $index . '" data-starred="true"></i>';
            } else {
                $star = '<span class="star-icon ion-ios7-star-outline font-size-22 star_' . $index . '"></span>';
            }

            $councilImage = ($row->logo_url != null ? $row->logo_url : BASE_URI . 'aiden-assets/images/aiden-anonymous.jpg');
            $council = $row->Council->getName();
            if ($row->getLodgeDate() != null) {
                $currentDateTime = $row->getLodgeDate()->format('c');
                $currentDate = $row->getLodgeDate()->format('d-m-Y');
            } else {
                $currentDateTime = $row->getCreated()->format('c');
                $currentDate = $row->getCreated()->format('d-m-Y');
            }
            $uploaded = '<span class="sub-title">
                                    <i class="ti-timer pdd-right-5"></i>
                                    <span>
                                        <time class="timeago" datetime="' . $currentDateTime . '">' . $currentDate . '</time>
                                    </span>
                                </span>';

            $councilLogo = '<img class="avatar" src="'.$councilImage .'">';

            $dataRow = array(
                "DT_RowId" => $row->getId(),
                "DT_RowClass" => ($row->getId() == $this->request->getQuery("currentViewedLead", "int") ? 'bg-secondary text-white' : '') . " row_" . $index . " context-menu row-container toggleInfo" . ($row->seen == 1 ? ' clicked ' : ''),
                $councilLogo,
                $council,
                $uploaded,
                ($row->getEstimatedCost() != 0 ? "$" . number_format($row->getEstimatedCost()) : ''),
//                ($searchFilterAll == false ? $row->applicantName : $this->getDasApplicant($row->getId())),
                $row->getHighlightedDescription($this->getUser()->Phrases),
            );

            $data[] = $dataRow;
            $index++;
        }


        // Output
        echo json_encode([
            "draw" => intval($this->request->getQuery("draw", "int")),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFilteredRows,
            "data" => $data
        ]);
    }

    public function leadsOldAction()
    {

        $requestedStatus = $this->request->getQuery("status", "int");
        $sqlParams = [];
        $sqlTypes = [];
        $leadStatuses = [];

        // Saved leads are only saved leads
        if ($requestedStatus == DasUsers::STATUS_SAVED) {
            $leadStatuses = [
                DasUsers::STATUS_SAVED
            ];
        } // If any other input, show all leads + saved
        else {
            $leadStatuses = [
                DasUsers::STATUS_LEAD,
                DasUsers::STATUS_SAVED
            ];
        }

        $totalRecords = count($this->getUser()->getDasByStatus($leadStatuses));
        $customSearchData = $this->request->getQuery("customSearch");
        $metadata = $customSearchData['metadata'];

        // metadata
        $metadataQuery = '';
        if ($metadata == 'false') {
            $metadataQuery = ' AND (SELECT COUNT(id) FROM das_documents WHERE das_id = `das`.`id`) > 1 ';
        }


        // Sort by these columns
        $columns = [
            false, // Checkbox: false = no sorting
            false, // Star: false = no sorting
            "`councils`.`name`",
            "`das`.`description`",
            "`das`.`lodge_date`"
        ];

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
            . " `das`.`created`,"
            . " `das_users`.`seen` as seen,"
            . " `das_users`.`status` as userStatus"
            . " FROM `das`"

            // Join DasUsers table
            . " INNER JOIN `das_users`"
            . " ON `das_users`.`das_id` = `das`.`id`"

            // Joins Councils table
            . " INNER JOIN `councils`"
            . " ON `councils`.`id` = `das`.`council_id`"
            . " WHERE 1=1"

            // Makes sure user has access to DA
            . " AND `das_users`.`users_id` = :users_id";

        // Documents condition
//            . " AND EXISTS (SELECT 1"
//            . " FROM `das_documents` docs"
//            . " WHERE docs.`das_id` = `das`.`id`)"
//            . $metadataQuery;


        $sqlParams["users_id"] = $this->getUser()->getId();
        $sqlTypes["users_id"] = \Phalcon\Db\Column::BIND_PARAM_INT;

        // Leads shows all leads, saved only saved leads.
        if ($requestedStatus != DasUsers::STATUS_LEAD) {

            $sql .= " AND `das_users`.`status` = :user_status";
            $sqlParams["user_status"] = $requestedStatus;
            $sqlTypes["user_status"] = \Phalcon\Db\Column::BIND_PARAM_INT;
        }

        // Check if we're searching
        $dtSearchArray = $this->request->getQuery("search");

        if (isset($dtSearchArray["value"]) && strlen($dtSearchArray["value"]) > 0) {

            $sql .= " AND `das`.`description` LIKE :search";
            $sqlParams["search"] = "%" . $dtSearchArray["value"] . "%";
            $sqlTypes["search"] = \Phalcon\Db\Column::BIND_PARAM_STR;
        }

        // Check if we're filtering seen (All, Read, Unread)
        $bSeenFilter = strlen($this->request->getQuery("tableFilter")) > 0;
        if ($bSeenFilter) {

            $addParams = false;
            $seenStatus = null;
            switch (strtolower($this->request->getQuery("tableFilter"))) {

                case "read":
                    $addParams = true;
                    $seenStatus = 1;
                    break;

                case "unread":
                    $addParams = true;
                    $seenStatus = 0;
                    break;
            }

            if ($addParams === true) {

                $sql .= " AND `das_users`.`seen` = :seen";
                $sqlParams["seen"] = $seenStatus;
                $sqlTypes["seen"] = \Phalcon\Db\Column::BIND_PARAM_INT;
            }
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
        } else {
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

        // Base model
        $das = new Das();
        $developmentApplicationRows = new \Phalcon\Mvc\Model\Resultset\Simple(
            null
            , $das
            , $das->getReadConnection()->query($sql, $sqlParams, $sqlTypes)
        );

        $data = [];
        $index = 1;
        foreach ($developmentApplicationRows as $da) {

            $checkbox = '<div class="checkbox">'
                . '<input id="select-checkbox-' . $da->getId() . '" type="checkbox" class="dt-checkbox" name="select-row">'
                . '<label for="select-checkbox-' . $da->getId() . '"></label>'
                . '</div>';

            if ((int)$da->userStatus === DasUsers::STATUS_SAVED) {
                $star = '<i class="star-icon ion-ios7-star font-size-22 starred" data-starred="true"></i>';
            } else {
                $star = '<i class="star-icon ion-ios7-star-outline font-size-22"></i>';
            }

            $dataRow = [
                "DT_RowId" => "lead_" . $da->getId(),
                "DT_RowClass" => ($da->seen ? "" : "bg-unread"),
                "DT_RowData" => ["seen" => (bool)$da->seen],
                "DT_RowClass" => $da->getId(),
                "DT_RowClass" => ($da->getId() == $this->request->getQuery("currentViewedLead", "int") ? 'bg-secondary text-white' : ''),
                "DT_RowClass" => 'row_' . $index,
                $checkbox,
                $star,
                $da->Council->getName(),
                $da->getHighlightedDescription($this->getUser()->Phrases),
                $da->getLodgeDate() ? $da->getLodgeDate()->format("d/m/Y") : "Unknown",
            ];

            $data[] = $dataRow;
            $index++;
        }

        // Output
        echo json_encode([
            "draw" => intval($this->request->getQuery("draw", "int")),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalFilteredRows,
            "data" => $data
        ]);

    }

    public function phrasesAction()
    {

        $customSearchData = $this->request->getQuery("customSearch");
        $filter = $customSearchData['filter'];
        $filterQuery = ($filter != '' ? ' AND `users_phrases`.`phrase` LIKE "%'.$filter.'%"' : '');
        // Used by sorting logic
//        $columns = [
//            false, // Checkbox
//            "`users_phrases`.`phrase`",
//            "`users_phrases`.`case_sensitive`",
//            "`users_phrases`.`literal_search`",
//            "`users_phrases`.`exclude_phrase`",
//            "(SELECT COUNT(*) FROM `das_phrases`"
//            . " INNER JOIN `das`"
//            . " ON `das`.`id` = `das_phrases`.`das_id`"
//            . " WHERE 1=1"
//            . " AND `das_phrases`.`phrases_id` = `users_phrases`.`id`)",
//        ];
        $columns = [
            "`users_phrases`.`phrase`",
            false,
            "`users_phrases`.`cost_from`",
            "`users_phrases`.`cost_to`"
        ];

        $sqlParams = $sqlTypes = [];
        $totalRecords = count($this->getUser()->Phrases); // Raw for consistency? Maybe...

        $sql = "SELECT"
            . " `users_phrases`.`id`,"
            . " `users_phrases`.`phrase`,"
            . " `users_phrases`.`councils`,"
            . " `users_phrases`.`cost_from`,"
            . " `users_phrases`.`cost_to`,"
            . " `users_phrases`.`case_sensitive`,"
            . " `users_phrases`.`literal_search`,"
            . " `users_phrases`.`exclude_phrase`,"
            . " `users_phrases`.`created`,"
            . " (SELECT COUNT(*) FROM `das_phrases`"
            . " INNER JOIN `das`"
            . " ON `das`.`id` = `das_phrases`.`das_id`"
            . " WHERE 1=1"
            . " AND `das_phrases`.`phrases_id` = `users_phrases`.`id`"
            . " AND `users_phrases`.`users_id` = :users_id"
            . ") as phraseOccurences"
            . " FROM `users_phrases`"
            . " WHERE 1=1"
            . $filterQuery
            . " AND `users_phrases`.`users_id` = :users_id";


        $sqlParams["users_id"] = $this->getUser()->getId();
        $sqlTypes["users_id"] = \Phalcon\Db\Column::BIND_PARAM_INT;

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
        } else {

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



            $action = '<i data-id="' . $phrase->getId() . '" class="cursor-pointer fa fa-edit editPhrase" title="Edit"></i>';

            if($phrase->getCouncils() != 'all'){
                $councils = [];
                $councilsArr = json_decode($phrase->getCouncils());
                for($c = 0; $c < count($councilsArr); $c++){
                    $councils[] = ucfirst(Councils::getCouncilById($councilsArr[$c])->getName());
                }

                $councils = implode(', ', $councils);
            }else{
                $councils = 'All';
            }

            $costTo = $phrase->getCostTo();
            if($costTo == 999999999999){
                $costTo = number_format(50000000).'+';
            }else{
                $costTo = ($phrase->getCostTo() != 0 ? "$" . number_format($phrase->getCostTo()) : '0');
            }

            $costFrom = $phrase->getCostFrom();
            if($costFrom == 999999999999){
                $costFrom = number_format(50000000).'+';
            }else{
                $costFrom = ($phrase->getCostFrom() != 0 ? "$" . number_format($phrase->getCostFrom()) : '0');
            }
            $dataRow = [
                'DT_RowId' => "phrase_" . $phrase->getId(),
                $phrase->getPhrase(),
                $councils,
                $costFrom,
                $costTo,
//                $phrase->getExcludePhrase() ? "" : $phrase->phraseOccurences,
                $action
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

    public function councilsAction()
    {

        $sqlTypes = $sqlParams = [];

        // Columns that are used by sorting logic
        $columns = [
            0 => false, // Checkbox
            1 => "`councils`.`name`",
            2 => "`councils`.`website_url`",
            3 => false
        ];

        // Columns to SELECT
        $sql = "SELECT"
            . " `councils`.`id`,"
            . " `councils`.`name`,"
            . " `councils`.`website_url`"
            . " FROM `councils`"
            . " WHERE 1=1";

        // Check if we're searching
        $dtSearchArray = $this->request->getQuery("search");

        if (isset($dtSearchArray["value"]) && strlen($dtSearchArray["value"]) > 0) {

            $filtering = true;

            $sql .= " AND (`councils`.`name` LIKE :search OR `councils`.`website_url` LIKE :search)";
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
        } else {

            $sql .= ' ORDER BY `councils`.`name` ASC';
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

            // Generate HTML URL
            $websiteUrl = sprintf('<a href="%1$s" target="_blank">%1$s</a>', $council->getWebsiteUrl());

            $data[] = [
                'DT_RowId' => sprintf("council_%s", $council->getId()),
                $rowCheckbox,
                $council->getName(),
                $websiteUrl,
                $this->getUser()->getSubscribedToCouncilText($council),
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


    public function getDasApplicant($dasId)
    {
        $dp = DasParties::find(
            [
                'conditions' => 'das_id = :das_id: AND role = :role:',
                'bind' => [
                    'das_id' => $dasId,
                    'role' => 'Applicant'
                ]
            ]
        );

        if ($dp) {
            $applicants = array();
            for ($x = 0; $x < count($dp); $x++) {
                $applicants[] = $dp[$x]->getName();
            }
            return implode(', ', $applicants);
        } else {
            return false;
        }
    }

}
