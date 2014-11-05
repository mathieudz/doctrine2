<?php


namespace Doctrine\Tests\ORM\Functional;


use Doctrine\ORM\UnitOfWork;

class MergeSharedEntitiesTest extends \Doctrine\Tests\OrmFunctionalTestCase {

    protected function setUp()
    {
        parent::setUp();

        try {
            $this->_schemaTool->createSchema(array(
                    $this->_em->getClassMetadata(__NAMESPACE__ . '\MSEFile'),
                    $this->_em->getClassMetadata(__NAMESPACE__ . '\MSEPicture'),
                ));
        } catch (\Exception $ignored) {
        }
    }

    public function testMergeSharedNewEntities() {

        /** @var MSEPicture $picture */
        $file = new MSEFile;

        $picture = new MSEPicture;
        $picture->setFile($file);
        $picture->setOtherFile($file);

        $em = $this->_em;

        $picture = $em->merge($picture);

        $this->assertEquals($picture->getFile(), $picture->getOtherFile(), "Identical entities must remain identical");
    }

    public function testMergeSharedManagedEntities() {

        /** @var MSEPicture $picture */
        $file = new MSEFile;

        $picture = new MSEPicture;
        $picture->setFile($file);
        $picture->setOtherFile($file);

        $em = $this->_em;
        $em->persist($file);
        $em->flush();
        $em->clear();

        $picture = $em->merge($picture);

        $this->assertEquals($picture->getFile(), $picture->getOtherFile(), "Identical entities must remain identical");
    }

    public function testMergeSharedManagedEntitiesSerialize() {

        /** @var MSEPicture $picture */
        $file = new MSEFile;

        $picture = new MSEPicture;
        $picture->setFile($file);
        $picture->setOtherFile($file);

        $serializedPicture = serialize($picture);

        $em = $this->_em;
        $em->persist($file);
        $em->flush();
        $em->clear();

        $picture = unserialize($serializedPicture);
        $picture = $em->merge($picture);

        $this->assertEquals($picture->getFile(), $picture->getOtherFile(), "Identical entities must remain identical");
    }

}

/**
 * @Entity
 */
class MSEPicture
{
    /**
     * @Column(name="picture_id", type="integer")
     * @Id @GeneratedValue
     */
    private $pictureId;

    /**
     * @ManyToOne(targetEntity="MSEFile", cascade={"persist", "merge"})
     * @JoinColumn(name="file_id", referencedColumnName="file_id")
     */
    private $file;

    /**
     * @ManyToOne(targetEntity="MSEFile", cascade={"persist", "merge"})
     * @JoinColumn(name="other_file_id", referencedColumnName="file_id")
     */
    private $otherFile;

    /**
     * Get pictureId
     */
    public function getPictureId()
    {
        return $this->pictureId;
    }

    /**
     * Set file
     */
    public function setFile($value = null)
    {
        $this->file = $value;
    }

    /**
     * Get file
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set file
     */
    public function setOtherFile($value = null)
    {
        $this->otherFile = $value;
    }

    /**
     * Get file
     */
    public function getOtherFile()
    {
        return $this->otherFile;
    }
}

/**
 * @Entity
 */
class MSEFile
{
    /**
     * @Column(name="file_id", type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    public $fileId;

    /**
     * Get fileId
     */
    public function getFileId()
    {
        return $this->fileId;
    }
}
