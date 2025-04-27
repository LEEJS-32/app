<?php

class SimplePager {
    public $limit;      // Page size
    public $page;       // Current page
    public $item_count; // Total item count
    public $page_count; // Total page count
    public $result;     // Result set (array of records)
    public $count;      // Item count on the current page

    public function __construct($query, $params, $limit, $page) {
        global $_db; // Use the global PDO database connection

        // Set limit and page
        $this->limit = ctype_digit((string)$limit) ? max((int)$limit, 1) : 10;
        $this->page = ctype_digit((string)$page) ? max((int)$page, 1) : 1;

        // Set item count
        $count_query = preg_replace('/SELECT.+FROM/i', 'SELECT COUNT(*) FROM', $query, 1);
        try {
            $stm = $_db->prepare($count_query);
            $stm->execute($params);
            $this->item_count = $stm->fetchColumn();
        } catch (PDOException $e) {
            die("Error preparing count query: " . $e->getMessage());
        }

        // Set page count
        $this->page_count = ceil($this->item_count / $this->limit);

        // Calculate offset
        $offset = ($this->page - 1) * $this->limit;

        // Set result
        try {
            $stm = $_db->prepare($query . " LIMIT :offset, :limit");
            foreach ($params as $key => $value) {
                $stm->bindValue($key, $value);
            }
            $stm->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stm->bindValue(':limit', $this->limit, PDO::PARAM_INT);
            $stm->execute();
            $this->result = $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error preparing result query: " . $e->getMessage());
        }

        // Set count
        $this->count = count($this->result);
    }

    public function html($href = '', $attr = '') {
        if (!$this->result) return '';

        // Generate pager HTML
        $prev = max($this->page - 1, 1);
        $next = min($this->page + 1, $this->page_count);

        $html = "<nav class='pager' $attr>";
        $html .= "<a href='?page=1&$href'>First</a>";
        $html .= "<a href='?page=$prev&$href'>Previous</a>";

        for ($p = 1; $p <= $this->page_count; $p++) {
            $active = $p == $this->page ? 'active' : '';
            $html .= "<a href='?page=$p&$href' class='$active'>$p</a>";
        }

        $html .= "<a href='?page=$next&$href'>Next</a>";
        $html .= "<a href='?page=$this->page_count&$href'>Last</a>";
        $html .= "</nav>";

        return $html;
    }
}