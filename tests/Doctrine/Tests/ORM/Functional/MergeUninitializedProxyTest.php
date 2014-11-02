<?php


namespace Doctrine\Tests\ORM\Functional;


use Doctrine\ORM\UnitOfWork;

class MergeUninitializedProxyTest extends \Doctrine\Tests\OrmFunctionalTestCase {

    protected function setUp()
    {
        parent::setUp();

        try {
            $this->_schemaTool->createSchema(array(
                    $this->_em->getClassMetadata(__NAMESPACE__ . '\MUPFile'),
                    $this->_em->getClassMetadata(__NAMESPACE__ . '\MUPPicture'),
                ));
        } catch (\Exception $ignored) {
        }
    }

    public function testMergeUnserializedIntoEntity() {

        $file = new MUPFile;

        $picture = new MUPPicture;
        $picture->setFile($file);

        $em = $this->_em;
        $em->persist($picture);
        $em->flush();
        $em->clear();

        $fileId = $file->getFileId();
        $pictureId = $picture->getPictureId();

        $picture = $em->find(__NAMESPACE__ . '\MUPPicture', $pictureId);
        $serializedPicture = serialize($picture);

        $em->clear();

        $file = $em->find(__NAMESPACE__ . '\MUPFile', $fileId);
        $picture = unserialize($serializedPicture);
        $picture = $em->merge($picture);

        $this->assertEquals($file, $picture->getFile(), "Unserialized proxy was not merged into managed entity");
    }

    public function testMergeDetachedIntoEntity() {

        $file = new MUPFile;

        $picture = new MUPPicture;
        $picture->setFile($file);

        $em = $this->_em;
        $em->persist($picture);
        $em->flush();
        $em->clear();

        $fileId = $file->getFileId();
        $pictureId = $picture->getPictureId();

        $picture = $em->find(__NAMESPACE__ . '\MUPPicture', $pictureId);

        $em->clear();

        $file = $em->find(__NAMESPACE__ . '\MUPFile', $fileId);
        $picture = $em->merge($picture);

        $this->assertEquals($file, $picture->getFile(), "Detached proxy was not merged into managed entity");
    }

    public function testMergeUnserializedIntoProxy() {

        $file = new MUPFile;

        $picture = new MUPPicture;
        $picture->setFile($file);

        $picture2 = new MUPPicture;
        $picture2->setFile($file);

        $em = $this->_em;
        $em->persist($picture);
        $em->persist($picture2);
        $em->flush();
        $em->clear();

        $pictureId = $picture->getPictureId();
        $picture2Id = $picture2->getPictureId();

        $picture = $em->find(__NAMESPACE__ . '\MUPPicture', $pictureId);
        $serializedPicture = serialize($picture);

        $em->clear();

        $picture2 = $em->find(__NAMESPACE__ . '\MUPPicture', $picture2Id);
        $picture = unserialize($serializedPicture);
        $picture = $em->merge($picture);

        $this->assertEquals($picture2->getFile(), $picture->getFile(), "Unserialized proxy was not merged into managed proxy");
    }

    public function testMergeDetachedIntoProxy() {

        $file = new MUPFile;

        $picture = new MUPPicture;
        $picture->setFile($file);

        $picture2 = new MUPPicture;
        $picture2->setFile($file);

        $em = $this->_em;
        $em->persist($picture);
        $em->persist($picture2);
        $em->flush();
        $em->clear();

        $pictureId = $picture->getPictureId();
        $picture2Id = $picture2->getPictureId();

        $picture = $em->find(__NAMESPACE__ . '\MUPPicture', $pictureId);

        $em->clear();

        $picture2 = $em->find(__NAMESPACE__ . '\MUPPicture', $picture2Id);
        $picture = $em->merge($picture);

        $this->assertEquals($picture2->getFile(), $picture->getFile(), "Detached proxy was not merged into managed proxy");
    }

}

/**
 * @Entity
 */
class MUPPicture
{
    /**
     * @Column(name="picture_id", type="integer")
     * @Id @GeneratedValue
     */
    private $pictureId;

    /**
     * @ManyToOne(targetEntity="MUPFile", cascade={"persist", "merge"})
     * @JoinColumn(name="file_id", referencedColumnName="file_id")
     */
    private $file;

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
}

/**
 * @Entity
 */
class MUPFile
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
