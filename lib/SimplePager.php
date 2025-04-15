<?php

class SimplePager {
    public $limit;      // Page size
    public $page;       // Current page
    public $item_count; // Total item count
    public $page_count; // Total page count
    public $result;     // Result set (array of records)
    public $count;      // Item count on the current page

    public function __construct($query, $params, $limit, $page) {
        global $conn; // Use the global database connection

        // Set limit and page
        $this->limit = ctype_digit((string)$limit) ? max((int)$limit, 1) : 10;
        $this->page = ctype_digit((string)$page) ? max((int)$page, 1) : 1;

        // Set item count
        $count_query = preg_replace('/SELECT.+FROM/i', 'SELECT COUNT(*) FROM', $query, 1);
        $stmt = $conn->prepare($count_query);
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Generate type string
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $stmt->bind_result($this->item_count);
        $stmt->fetch();
        $stmt->close();

        // Set page count
        $this->page_count = ceil($this->item_count / $this->limit);

        // Calculate offset
        $offset = ($this->page - 1) * $this->limit;

        // Set result
        $stmt = $conn->prepare($query . " LIMIT ?, ?");
        if (!empty($params)) {
            $types = str_repeat('s', count($params)) . 'ii'; // Add 'ii' for offset and limit
            $stmt->bind_param($types, ...array_merge($params, [$offset, $this->limit]));
        } else {
            $stmt->bind_param('ii', $offset, $this->limit);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $this->result = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

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