<?php

class ProductDetails
{
    private $imageLinks = array();
    private $name;
    private $address = array();
    private $description;
    private $Price;
    private $Rooms;
    private $Surface;
    private $Bathroom;
    private $floor;
    private $Features = array();
    private $Expenses = array();
    private $EnergyEfficiency = array();

    // Empty constructor
    public function __construct()
    {
        //Do nothing
    }

    // Setters
    public function setImageLinks($imageLinks)
    {
        $this->imageLinks = $imageLinks;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setPrice($price)
    {
        $this->Price = $price;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setFeatures($features)
    {
        $this->Features = $features;
    }

    public function setExpenses($expenses)
    {
        $this->Expenses = $expenses;
    }

    public function setEnergyEfficiency($energyEfficiency)
    {
        $this->EnergyEfficiency = $energyEfficiency;
    }

    // Getters
    public function getImageLinks()
    {
        return $this->imageLinks;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPrice()
    {
        return $this->Price;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getFeatures()
    {
        return $this->Features;
    }

    public function getExpenses()
    {
        return $this->Expenses;
    }

    public function getEnergyEfficiency()
    {
        return $this->EnergyEfficiency;
    }



    public function setRooms($rooms)
    {
        $this->Rooms = $rooms;
    }

    public function getRooms()
    {
        return $this->Rooms;
    }

    public function setSurface($surface)
    {
        $this->Surface = $surface;
    }

    public function getSurface()
    {
        return $this->Surface;
    }

    public function setBathroom($bathroom)
    {
        $this->Bathroom = $bathroom;
    }

    public function getBathroom()
    {
        return $this->Bathroom;
    }

    public function setFloor($floor)
    {
        $this->floor = $floor;
    }

    public function getFloor()
    {
        return $this->floor;
    }
}
