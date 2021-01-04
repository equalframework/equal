<?php

class GeoIP {
    
    private $pdo ;

    public function __construct($filename) {
        $this->pdo = new PDO("sqlite:$filename") ;
        
        // Parce que je préfère les exceptions :
        $this->pdo->setAttribute(
            \PDO::ATTR_ERRMODE,
            \PDO::ERRMODE_EXCEPTION
        ) ;

        $this->initIPv4() ;
        $this->initIPv6() ;
    }
    
    public function getIP($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $this->getIPv4($ip) ;
        } 
        else if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->getIPv6($ip) ;
        }        
        throw new Exception("Not a valid IP Address") ;        
    }
    
    
    public function addIP($ip, $coordinates) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $this->addIPv4($ip, $coordinates) ;
        } 
        else if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->addIPv6($ip, $coordinates) ;
        }        
        throw new Exception("Not a valid IP Address") ;        
        
    }

    private static function ip6toBigInt($ip) {
        $bin = inet_pton($ip) ;
        $ints = unpack("J2", $bin) ;
        return $ints[1] ;
    }
    
    private function addIPv4($ip, $coordinates) {
        $addr = ip2long($ip) ;
        $st = $this->pdo->prepare(
            "INSERT INTO IPv4 (address, coordinates)"
            ." VALUES (:address, :coordinates)"
        );
        $st->execute([
            "address"       => $addr,
            "coordinates"   => $coordinates
        ]);
    }

    private function addIPv6($ip, $coordinates) {
        $addr = self::ip6toBigInt($ip);
        $st = $this->pdo->prepare(
            "INSERT INTO IPv6 (address, coordinates)"
            . " VALUES (:address, :coordinates)"
        );
        $st->execute([
            "address"       => $addr,
            "coordinates"   => $coordinates
        ]);
    }
    
    private function getIPv4($ip) {
        $value = ip2long($ip) ;
        $st = $this->pdo->prepare("SELECT * from IPv4 WHERE address = :value") ;
        $st->execute(["value" => $value]);
        $row = $st->fetch() ;
        return $row["coordinates"] ?? null;
    }

    private function getIPv6($ip) {
        $value = static::ip6toBigInt($ip) ;
        $st = $this->pdo->prepare("SELECT * from IPv6 WHERE address = :value") ;
        $st->execute(["value" => $value]) ;
        $row = $st->fetch() ;
        return $row["coordinates"] ?? null;
    }    
    
    private function initIPv4() {
        $this->pdo->query("
            CREATE table if not exists IPv4 (
                address   int unsigned,
                coordinates BLOB
        )") ;
        $this->pdo->query("
            CREATE index if not exists index_ipv4 on IPv4(address)
        ") ;
    }
    
    private function initIPv6() {
        $this->pdo->query("
            CREATE table if not exists IPv6 (
                address   bigint unsigned,
                coordinates BLOB
        )") ;                
        $this->pdo->query("
            CREATE index if not exists index_ipv6 on IPv6(address)
        ") ;
    }    
}