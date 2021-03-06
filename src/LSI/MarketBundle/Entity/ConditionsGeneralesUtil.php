<?php

namespace LSI\MarketBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ConditionsGeneralesUtil
 *
 * @ORM\Table(name="cgu")
 * @ORM\Entity(repositoryClass="LSI\MarketBundle\Repository\ConditionsGeneralesUtilRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ConditionsGeneralesUtil {

    private $nomFichier = 'conditions_generales_utilisations';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="cgu", type="string", length=225)
     */
    private $cgu;

    /**
     * @var string
     *
     * @ORM\Column(name="cgu_alt", type="string", length=225)
     */
    private $cguAlt;

    /**
     * @var UploadedFile
     * @Assert\File(
     *     maxSize = "5M",
     *     mimeTypes = {"application/pdf"},
     *     mimeTypesMessage = "Veuillez charger un fichier PDF valide"
     * )
     */
    private $fichier;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    // On ajoute cet attribut pour y stocker le nom du fichier temporairement
    private $tempFilename;

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        // Si jamais il n'y a pas de fichier (champ facultatif), on ne fait rien
        if (null === $this->fichier) {
            return;
        }

        // Le nom du fichier est son id, on doit juste stocker également son extension
        // Pour faire propre, on devrait renommer cet attribut en « extension », plutôt que « url »
        $this->cgu = $this->fichier->guessExtension();

        // Et on génère l'attribut alt de la balise <img>, à la valeur du nom du fichier sur le PC de l'internaute
        //$this->cguAlt = $this->fichier->getClientOriginalName();
        $this->cguAlt = $this->nomFichier;
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        // Si jamais il n'y a pas de fichier (champ facultatif), on ne fait rien
        if (null === $this->fichier) {
            return;
        }

        // Si on avait un ancien fichier (attribut tempFilename non null), on le supprime
        if (null !== $this->tempFilename) {
            $oldFile = $this->getUploadRootDir().'/'.$this->nomFichier.'.'.$this->tempFilename;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // On déplace le fichier envoyé dans le répertoire de notre choix
        $this->fichier->move(
            $this->getUploadRootDir(), // Le répertoire de destination
            $this->nomFichier.'.'.$this->cgu   // Le nom du fichier à créer, ici « id.extension »
        );
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
        // On sauvegarde temporairement le nom du fichier, car il dépend de l'id
        $this->tempFilename = $this->getUploadRootDir().'/'.$this->nomFichier.'.'.$this->cgu;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        // En PostRemove, on n'a pas accès à l'id, on utilise notre nom sauvegardé
        if (file_exists($this->tempFilename)) {
            // On supprime le fichier
            unlink($this->tempFilename);
        }
    }

    public function getUploadDir()
    {
        // On retourne le chemin relatif vers l'image pour un navigateur (relatif au répertoire /web donc)
        return 'uploads/conditions';
    }

    protected function getUploadRootDir()
    {
        // On retourne le chemin relatif vers l'image pour notre code PHP
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    public function getWebPath()
    {
        return $this->getUploadDir().'/'.$this->nomFichier.'.'.$this->getCgu();
    }

    /**
     * @return UploadedFile
     */
    public function getFichier()
    {
        return $this->fichier;
    }

    /**
     * @param UploadedFile $fichier
     */
    // On modifie le setter de Fichier, pour prendre en compte l'upload d'un fichier lorsqu'il en existe déjà un autre
    public function setFichier(UploadedFile $fichier)
    {
        $this->fichier = $fichier;

        // On vérifie si on avait déjà un fichier pour cette entité
        if (null !== $this->cgu) {
            // On sauvegarde l'extension du fichier pour le supprimer plus tard
            $this->tempFilename = $this->cgu;

            // On réinitialise les valeurs des attributs url et alt
            $this->cgu = null;
            $this->cguAlt = null;
        }
    }


    /**
     * Set cgu
     *
     * @param string $cgu
     *
     * @return ConditionsGeneralesUtil
     */
    public function setCgu($cgu)
    {
        $this->cgu = $cgu;

        return $this;
    }

    /**
     * Get cgu
     *
     * @return string
     */
    public function getCgu()
    {
        return $this->cgu;
    }

    /**
     * Set cguAlt
     *
     * @param string $cguAlt
     *
     * @return ConditionsGeneralesUtil
     */
    public function setCguAlt($cguAlt)
    {
        $this->cguAlt = $cguAlt;

        return $this;
    }

    /**
     * Get cguAlt
     *
     * @return string
     */
    public function getCguAlt()
    {
        return $this->cguAlt;
    }
}
