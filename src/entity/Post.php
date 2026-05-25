<?php

   class Post {
      public function __construct(
	 private string $contenu,
	 private int $id_user,
	 private ?int $id_group = null,
	 private ?DateTimeImmutable $date_de_creation = null,
	 private ?int $id = null
      ) {
	 // Default to current date/time if not provided
	 $this->date_de_creation = $date_de_creation ?? new DateTimeImmutable();
      }
      public function getIdUser(){
	 return $this->id_user;
      }

      public function getIdGroup(){
	 return $this->id_group;
      }

      public function getContenu(){
	 return $this->contenu;
      }
      public function getDateDeCreation(){
	 return $this->date_de_creation;
      }

      /**
      * For Debugging
      */
      public function __toString(): string {
	 return sprintf(
	    "Post [%s] | Author ID: %d | Group ID: %s | Date: %s | Content: %s...",
	    $this->id ?? 'NEW',
	    $this->id_user,
	    $this->id_group ?? 'Global',
	    $this->date_de_creation->format('Y-m-d H:i'),
	    substr(htmlspecialchars($this->contenu), 0, 30) // Show first 30 chars
	 );
      }
      public function toHtml(): string {
	 $content = nl2br(htmlspecialchars($this->contenu));
	 $date = $this->date_de_creation->format('d M Y, H:i');

	 return "
	 <div class='post-card' style='border: 1px solid #ddd; margin-bottom: 20px; padding: 15px;'>
	    <p class='post-meta'>
	    <small>Posté par l'utilisateur #{$this->id_user} le {$date}</small>
	    </p>
	    <div class='post-content'>
	       {$content}
	    </div>
	 </div>
	 ";
      }
   }

?>
