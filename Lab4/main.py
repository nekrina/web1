from imageai.Detection import ObjectDetection
import os

execution_path = os.getcwd()

detector = ObjectDetection()
detector.setModelTypeAsYOLOv3()
detector.setModelPath( os.path.join(execution_path, "resnet50_coco_best_v2.1.0.h5"))
detector.loadModel()

detections, objects_path = detector.detectObjectsFromImage(input_image=os.path.join(execution_path, "test1.jpg"), output_image_path=os.path.join(execution_path , "testnew.jpg"), minimum_percentage_probability=30,  extract_detected_objects=True)
