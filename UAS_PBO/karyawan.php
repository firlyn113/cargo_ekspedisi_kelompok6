<?php

abstract class Karyawan {

    protected $id_karyawan;
    protected $nama_karyawan;
    protected $departemen;
    protected $hari_kerja_masuk;
    protected $gaji_dasar_per_hari;


    public function __construct(
        $id_karyawan,
        $nama_karyawan,
        $departemen,
        $hari_kerja_masuk,
        $gaji_dasar_per_hari
    ){

        $this->id_karyawan = $id_karyawan;
        $this->nama_karyawan = $nama_karyawan;
        $this->departemen = $departemen;
        $this->hari_kerja_masuk = $hari_kerja_masuk;
        $this->gaji_dasar_per_hari = $gaji_dasar_per_hari;

    }


    // abstract method tanpa isi
    abstract public function hitungGajiBersih();

    abstract public function gajiDasarPerHari();



    // getter
    public function getNama()
    {
        return $this->nama_karyawan;
    }


    public function getDepartemen()
    {
        return $this->departemen;
    }


}

?>