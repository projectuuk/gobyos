<?php
class Analytics {
    private $conn;
    private $table_name = "analytics_visits";

    public $id;
    public $ip_address;
    public $user_agent;
    public $page_url;
    public $page_title;
    public $referrer;
    public $session_id;
    public $browser;
    public $operating_system;
    public $device_type;
    public $visit_time;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY visit_time DESC LIMIT 1000";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET ip_address=:ip_address, user_agent=:user_agent, page_url=:page_url,
                      page_title=:page_title, referrer=:referrer, session_id=:session_id,
                      browser=:browser, operating_system=:operating_system, device_type=:device_type";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->ip_address = htmlspecialchars(strip_tags($this->ip_address));
        $this->user_agent = htmlspecialchars(strip_tags($this->user_agent));
        $this->page_url = htmlspecialchars(strip_tags($this->page_url));
        $this->page_title = htmlspecialchars(strip_tags($this->page_title));
        $this->referrer = htmlspecialchars(strip_tags($this->referrer));
        $this->session_id = htmlspecialchars(strip_tags($this->session_id));
        $this->browser = htmlspecialchars(strip_tags($this->browser));
        $this->operating_system = htmlspecialchars(strip_tags($this->operating_system));
        $this->device_type = htmlspecialchars(strip_tags($this->device_type));

        // Bind values
        $stmt->bindParam(":ip_address", $this->ip_address);
        $stmt->bindParam(":user_agent", $this->user_agent);
        $stmt->bindParam(":page_url", $this->page_url);
        $stmt->bindParam(":page_title", $this->page_title);
        $stmt->bindParam(":referrer", $this->referrer);
        $stmt->bindParam(":session_id", $this->session_id);
        $stmt->bindParam(":browser", $this->browser);
        $stmt->bindParam(":operating_system", $this->operating_system);
        $stmt->bindParam(":device_type", $this->device_type);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    function getSummary() {
        $summary = array();
        
        // Total visits
        $query = "SELECT COUNT(*) as total_visits FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $summary['total_visits'] = $row['total_visits'];
        
        // Unique visitors (by IP)
        $query = "SELECT COUNT(DISTINCT ip_address) as unique_visitors FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $summary['unique_visitors'] = $row['unique_visitors'];
        
        // Today's visits
        $query = "SELECT COUNT(*) as today_visits FROM " . $this->table_name . " 
                  WHERE DATE(visit_time) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $summary['today_visits'] = $row['today_visits'];
        
        // This month's visits
        $query = "SELECT COUNT(*) as month_visits FROM " . $this->table_name . " 
                  WHERE YEAR(visit_time) = YEAR(CURDATE()) AND MONTH(visit_time) = MONTH(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $summary['month_visits'] = $row['month_visits'];
        
        return $summary;
    }

    function getDailyVisits($days = 30) {
        $query = "SELECT DATE(visit_time) as date, COUNT(*) as visits 
                  FROM " . $this->table_name . " 
                  WHERE visit_time >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                  GROUP BY DATE(visit_time) 
                  ORDER BY date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $days);
        $stmt->execute();
        
        $daily_data = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $daily_data[] = $row;
        }
        
        return $daily_data;
    }

    function getPopularPages($limit = 10) {
        $query = "SELECT page_url, page_title, COUNT(*) as visits 
                  FROM " . $this->table_name . " 
                  WHERE page_url != '' 
                  GROUP BY page_url, page_title 
                  ORDER BY visits DESC 
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit);
        $stmt->execute();
        
        $popular_pages = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $popular_pages[] = $row;
        }
        
        return $popular_pages;
    }

    function getTopReferrers($limit = 10) {
        $query = "SELECT referrer, COUNT(*) as visits 
                  FROM " . $this->table_name . " 
                  WHERE referrer != '' AND referrer NOT LIKE '%{$_SERVER['HTTP_HOST']}%'
                  GROUP BY referrer 
                  ORDER BY visits DESC 
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit);
        $stmt->execute();
        
        $referrers = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $referrers[] = $row;
        }
        
        return $referrers;
    }

    function getBrowserStats() {
        $query = "SELECT browser, COUNT(*) as visits 
                  FROM " . $this->table_name . " 
                  WHERE browser != '' 
                  GROUP BY browser 
                  ORDER BY visits DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $browsers = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $browsers[] = $row;
        }
        
        return $browsers;
    }

    function parseUserAgent($user_agent) {
        $browser = 'Unknown';
        $os = 'Unknown';
        $device = 'Desktop';
        
        // Detect browser
        if (strpos($user_agent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($user_agent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($user_agent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($user_agent, 'Edge') !== false) {
            $browser = 'Edge';
        } elseif (strpos($user_agent, 'Opera') !== false) {
            $browser = 'Opera';
        }
        
        // Detect OS
        if (strpos($user_agent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($user_agent, 'Mac') !== false) {
            $os = 'macOS';
        } elseif (strpos($user_agent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($user_agent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($user_agent, 'iOS') !== false) {
            $os = 'iOS';
        }
        
        // Detect device type
        if (strpos($user_agent, 'Mobile') !== false || strpos($user_agent, 'Android') !== false) {
            $device = 'Mobile';
        } elseif (strpos($user_agent, 'Tablet') !== false || strpos($user_agent, 'iPad') !== false) {
            $device = 'Tablet';
        }
        
        return array(
            'browser' => $browser,
            'os' => $os,
            'device' => $device
        );
    }
}
?>

