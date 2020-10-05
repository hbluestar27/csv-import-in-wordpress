<?php
class Import {
    private $wpdb;

    public function __construct($wpdb) {
        $this->wpdb = $wpdb;
    }

    public function importPrice($file) {
        $max_time = ini_get('max_execution_time');
        ini_set('max_execution_time', '0');

        $sku = $this->getSkues();
        $m_data = $this->getDataFromFile($file, $sku);
        $result = $this->insertData($m_data);

        ini_set('max_execution_time', $max_time);
        return $result;
    }

    public function insertData($m_data) {
        $return_arr = array();
        foreach($m_data as $key => $item) {
            $sql = "SELECT pm.meta_key, pm.meta_value
                    FROM
                        (SELECT p.ID
                        FROM wpnh_posts AS p
                            INNER JOIN wpnh_postmeta AS pm ON (p.ID = pm.post_id)
                        WHERE  pm.meta_key = '_sku' AND pm.meta_value = '".$key."') AS p
                        INNER JOIN wpnh_postmeta AS pm ON (p.ID = pm.post_id)
                    WHERE (pm.meta_key = 'metal_for_prodcut' OR pm.meta_key = 'otbj_prodcut_metal_price')
                    ORDER BY pm.meta_key";
            $result = $this->wpdb->get_results($sql, ARRAY_A);
            if(count($result) != 2) continue;
            
            $price_kind = explode("~~", $result[0]['meta_value']);
            $price_value = (array)json_decode($result[1]['meta_value']);
            
            $base_price = (int)$item['Sterling']; // <<Sterling>> is item name of Access database
            foreach($item as &$sub) {
                if($base_price != (int)$sub) $sub = (int)$sub - $base_price;
                else $sub = 0;
            }

            foreach($price_kind as $sub) {
                foreach($item as $arr_key => $value) {
                    if(strpos(strtolower($sub), strtolower($arr_key)) !== false){
                        $price_value[$sub] = $value;
                        break;
                    }    
                }
            }

            $sql = 'SELECT post_id FROM wpnh_postmeta WHERE meta_value = "'.$key.'";';
            $result = $this->wpdb->get_results($sql, ARRAY_A);
            if(count($result) != 1) continue;

            $post_id = $result[0]['post_id'];
            $price_value_str = json_encode($price_value);

            $return_arr[] = $key;

            $this->wpdb->query($this->wpdb->prepare("UPDATE wpnh_postmeta SET meta_value = '".$base_price."' WHERE post_id = ".$post_id." AND meta_key = '_price';"));
            $this->wpdb->query($this->wpdb->prepare("UPDATE wpnh_postmeta SET meta_value = '".$base_price."' WHERE post_id = ".$post_id." AND meta_key = '_regular_price';"));
            $this->wpdb->query($this->wpdb->prepare("UPDATE wpnh_postmeta SET meta_value = '".$price_value_str."' WHERE post_id = ".$post_id." AND meta_key = 'otbj_prodcut_metal_price';"));
        }

        return json_encode($return_arr);
    }

    public function getSkues() {
        $sql = "SELECT
                    DISTINCT(pm.meta_value) AS sku
                FROM
                    wpnh_posts AS p
                    INNER JOIN wpnh_postmeta AS pm ON (p.ID = pm.post_id)
                WHERE
                    p.post_type = 'product' AND
                    pm.meta_key = '_sku'";
        $result = $this->wpdb->get_results($sql, ARRAY_A);

        $record = array();
        foreach($result as $item) {
            $record[] = trim($item['sku']);
        }
        return $record;
    }

    public function getDataFromFile($file, $sku) {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if(empty($file['name']) || $extension != 'csv') return null;
      
        $csvFile = fopen($file['tmp_name'], 'r');
        $tmp = array();
        // Read file
        //p.ProductID = 0, p.ProductDescription = 1, p.Retail =2, ps.PuzzleStyleID = 3, pr.Gender = 4, 
        //ps.PuzzleStyle = 5, ps.Notes = 6, mc.MetalClassSKU = 7, mc.MetalClass = 8
        while(($csvData = fgetcsv($csvFile)) !== FALSE){
            $csvData = array_map("utf8_encode", $csvData);
            $dataLen = count($csvData);
    
            if(!($dataLen == 9)) continue;
            if(!in_array(trim($csvData[6]), $sku)) continue;

            $tmp[trim($csvData[6])][trim($csvData[8])] = trim($csvData[2]);
        }

		return $tmp;
	}
}
?>