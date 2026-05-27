<?php
class Group
{
	public function __construct(
		private string $nom,
		private ?DateTimeImmutable $date_creation = null,
		private ?int $moderateur = null
	) {
		$this->date_creation = $date_creation ?? new DateTimeImmutable();
	}

<<<<<<< HEAD
      public function getNom(){
	 return $this->nom;
      }
      public function __toString(): string {
	 return sprintf(
	    "Group: %s | Created: %s | Mod ID: %s",
	    $this->nom,
	    $this->date_creation->format('Y-m-d'),
	    $this->moderateur ?? 'None'
	 );
      }
   }

   ?>
=======
	public function getNom()
	{
		return $this->nom;
	}
	public function __toString(): string
	{
		return sprintf(
			"Group: %s | Created: %s | Mod ID: %s",
			$this->nom,
			$this->date_creation->format('Y-m-d'),
			$this->moderateur ?? 'None'
		);
	}
}
// 1. Using defaults (Date becomes today, Mod is null)
/* $group1 = new Group("PHP Enthusiasts"); */
/* echo $group1 . "<br>";  */
/**/
/* // 2. Providing everything */
/* $group2 = new Group("Admin Group", new DateTimeImmutable("2023-01-01"), 42); */
/* echo $group2; */
?>
>>>>>>> 56bcd12fcd57ae6195b0122a6ed19a38b8727d3c
