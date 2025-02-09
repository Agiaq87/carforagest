<?php

class ImageHandler
{
    // Dimensioni predefinite per PrestaShop 8
    private const IMAGE_SIZES = [
        'small' => ['width' => 45, 'height' => 45],
        'medium' => ['width' => 98, 'height' => 98],
        'large' => ['width' => 150, 'height' => 150]
    ];

    private $source_image;
    private $image_type;
    private $image_resource;
    private $temp_file;

    /**
     * Costruttore che accetta il percorso dell'immagine o un'immagine caricata
     * @param string|array $image Path dell'immagine o array $_FILES
     */
    public function __construct($image)
    {
        if (is_array($image) && isset($image['tmp_name'])) {
            $this->temp_file = true;
            $this->source_image = $image['tmp_name'];
        } else {
            $this->temp_file = false;
            $this->source_image = $image;
        }

        if (!file_exists($this->source_image)) {
            throw new Exception('File immagine non trovato: ' . $this->source_image);
        }

        $this->image_type = exif_imagetype($this->source_image);

        if (!in_array($this->image_type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF])) {
            throw new Exception('Tipo di immagine non supportato');
        }
    }

    /**
     * Carica l'immagine nella memoria
     */
    private function loadImage(): array
    {
        // Verifica se l'immagine è già stata caricata
        if ($this->image_resource) {
            return [
                'status' => true,
                'message' => 'L\'immagine è gia stata caricata'
            ];
        }

        switch ($this->image_type) {
            case IMAGETYPE_JPEG:
                $this->image_resource = imagecreatefromjpeg($this->source_image);
                break;
            case IMAGETYPE_PNG:
                $this->image_resource = imagecreatefrompng($this->source_image);
                break;
            case IMAGETYPE_GIF:
                $this->image_resource = imagecreatefromgif($this->source_image);
                break;
        }

        if (!$this->image_resource) {
            return [
                'status' => false,
                'message' => 'Impossibile caricare l\'immagine'
            ];
        }

        // Mantieni la trasparenza per PNG e GIF
        if (in_array($this->image_type, [IMAGETYPE_PNG, IMAGETYPE_GIF])) {
            imagealphablending($this->image_resource, true);
            imagesavealpha($this->image_resource, true);
        }

        return [
            'status' => true,
            'message' => 'Immagine caricata'
        ];
    }

    /**
     * Ridimensiona e salva l'immagine nei vari formati
     * @param int $brand_id ID del marchio
     * @return bool
     */
    public function processImage($brand_id): array
    {
        try {
            $this->loadImage();

            // Verifica e crea la directory se necessario
            if (!is_dir(_PS_MANU_IMG_DIR_)) {
                if (!mkdir(_PS_MANU_IMG_DIR_, 0755, true)) {
                    return [
                        'status' => false,
                        'message' => 'Impossibile creare la directory delle immagini'
                    ];
                }
            }

            foreach (self::IMAGE_SIZES as $size => $dimensions) {
                $resized = $this->resize($dimensions['width'], $dimensions['height']);

                // Nome file secondo la convenzione di PrestaShop
                $filename = $brand_id . ($size !== 'large' ? '-' . $size : '') . '.jpg';
                $output_path = _PS_MANU_IMG_DIR_ . $filename;

                // Imposta la qualità JPEG appropriata
                $quality = 95;

                // Gestisci la trasparenza prima del salvataggio
                if (in_array($this->image_type, [IMAGETYPE_PNG, IMAGETYPE_GIF])) {
                    $background = imagecreatetruecolor(imagesx($resized), imagesy($resized));
                    $white = imagecolorallocate($background, 255, 255, 255);
                    imagefilledrectangle($background, 0, 0, imagesx($resized), imagesy($resized), $white);
                    imagecopy($background, $resized, 0, 0, 0, 0, imagesx($resized), imagesy($resized));
                    imagedestroy($resized);
                    $resized = $background;
                }

                // Salva l'immagine
                if (!imagejpeg($resized, $output_path, $quality)) {
                    return [
                        'status' => false,
                        'message' => 'Impossibile salvare l\'immagine: ' . $output_path
                    ];
                }

                // Imposta i permessi corretti
                chmod($output_path, 0644);

                imagedestroy($resized);
            }

            return [
                'status' => true,
                'message' => 'Immagine caricata'
            ];

        } catch (Exception $e) {
            if (is_resource($resized)) {
                imagedestroy($resized);
            }
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Ridimensiona l'immagine mantenendo le proporzioni
     */
    private function resize($max_width, $max_height): array
    {
        $original_width = imagesx($this->image_resource);
        $original_height = imagesy($this->image_resource);

        // Calcola le nuove dimensioni mantenendo le proporzioni
        $ratio = min($max_width / $original_width, $max_height / $original_height);
        $new_width = (int)round($original_width * $ratio);
        $new_height = (int)round($original_height * $ratio);

        // Crea la nuova immagine
        $new_image = imagecreatetruecolor($new_width, $new_height);

        // Gestione della trasparenza
        if (in_array($this->image_type, [IMAGETYPE_PNG, IMAGETYPE_GIF])) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
        }

        // Ridimensiona con antialiasing
        $result = imagecopyresampled(
            $new_image, $this->image_resource,
            0, 0, 0, 0,
            $new_width, $new_height,
            $original_width, $original_height
        );

        if (!$result) {
            return [
                'status' => false,
                'message' => 'Impossibile salvare l\'immagine'
            ];
        }

        return [
            'status' => true,
            'message' => 'Immagine caricata',
            'data' => $new_image
        ];
    }

    /**
     * Pulisce la memoria
     */
    public function __destruct()
    {
        if (is_resource($this->image_resource)) {
            imagedestroy($this->image_resource);
        }
    }
}