<?php

class Database {

    protected $koneksi;

    public function __construct()
    {
        $this->koneksi = mysqli_connect(
            "localhost",
            "root",
            "",
            "db_uas_pbo_trpl1b_lanjuwidhunuastuti"
        );

        if (!$this->koneksi) {
            die("Koneksi gagal : " . mysqli_connect_error());
        }
    }

    public function getKoneksi()
    {
        return $this->koneksi;
    }
}

?>