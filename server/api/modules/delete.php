<?php

require_once 'global.php';

class Delete extends GlobalMethods
{

    private $pdo;
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function delete_user($id)
    {
        $sql = "DELETE FROM user WHERE id = ?";
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(
                [
                    $id
                ]
            );
            $this->pdo->commit();
            return $this->sendPayload(null, "success", "Successfully deleted record", 200);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $errmsg = $e->getMessage();
            $code = 400;
            return $this->sendPayload(null, "failed", $errmsg, $code);
        }
    }


    public function unassignCoordinatorFromClass($id, $class)
    {
        $sql = "DELETE FROM rl_class_coordinators
                WHERE coordinator_id = :coordinator_id AND block_name = :block_name";

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':coordinator_id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':block_name', $class, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                return $this->sendPayload(null, 'success', "Successfully deleted the class assignment.", 200);
            } else {
                $this->pdo->rollBack();
                return $this->sendPayload(null, 'failed', "No class assignment found for the provided coordinator ID and block name.", 404);
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }

    public function deleteSubmission($id, $table)
    {
        $sql = "DELETE FROM $table
                WHERE id = :id";

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->pdo->commit();
            return $this->sendPayload(null, "success", "Successfully deleted record", 200);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }

    public function removeStudentFromCompany($company_id, $student_id)
    {
        $sql = "DELETE FROM rl_company_students
                WHERE company_id = :company_id AND student_id = :student_id";

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                $this->unassignJob($student_id);
                $this->removeStudentFromSupervisor($student_id);
                return $this->sendPayload(null, 'success', "Successfully removed student from company.", 200);
            } else {
                $this->pdo->rollBack();
                return $this->sendPayload(null, 'failed', "No student found from company", 404);
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }

    public function removeStudentFromSupervisor($student_id, $supervisor_id = null)
    {
        $sql = "DELETE FROM rl_supervisor_students
                WHERE student_id = :student_id";

        if ($supervisor_id) {
            $sql .= " AND supervisor_id = :supervisor_id";
        }

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            if ($supervisor_id) {
                $stmt->bindParam(':supervisor_id', $supervisor_id, PDO::PARAM_INT);
            }
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                return $this->sendPayload(null, 'success', "Successfully removed student from supervisor selection.", 200);
            } else {
                $this->pdo->rollBack();
                return $this->sendPayload(null, 'failed', "No student found from supervisor selection", 404);
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }


    public function unassignJob($student_id)
    {
        $sql = "DELETE FROM student_jobs
                WHERE student_id = :student_id";

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                return $this->sendPayload(null, 'success', "Successfully unassigned job to student", 200);
            } else {
                $this->pdo->rollBack();
                return $this->sendPayload(null, 'failed', "No student found", 404);
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }

    public function unassignSchedules($student_id)
    {
        $sql = "DELETE FROM student_ojt_schedules
                WHERE student_id = :student_id";

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                return $this->sendPayload(null, 'success', "Successfully unassigned schedules to student", 200);
            } else {
                $this->pdo->rollBack();
                return $this->sendPayload(null, 'failed', "No student found", 404);
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }


    public function deleteHiringRequest($id)
    {
        $sql = "DELETE FROM company_hiring_requests
                WHERE id = :request_id";

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':request_id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                return $this->sendPayload(null, 'success', "Successfully deleted hiring request.", 200);
            } else {
                $this->pdo->rollBack();
                return $this->sendPayload(null, 'failed', "No hiring request found", 404);
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }

    public function deleteAvatar($id)
    {
        $sql = "DELETE FROM user_avatars
                WHERE user_id = :id";

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->pdo->commit();
            return $this->sendPayload(null, "success", "Successfully deleted avatar", 200);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }
    public function deleteLogo($id)
    {
        $sql = "DELETE FROM company_logos
                WHERE company_id = :id";

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->pdo->commit();
            return $this->sendPayload(null, "success", "Successfully deleted logo", 200);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }

    public function deleteSeminarRecord($id)
    {
        $sql = "DELETE FROM student_seminar_records
                WHERE id = :id";
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                return $this->sendPayload(null, 'success', "Successfully deleted record.", 200);
            } else {
                $this->pdo->rollBack();
                return $this->sendPayload(null, 'failed', "No record found", 404);
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }

    public function cancelJoinRequest($id)
    {
        $sql = "DELETE FROM class_join_requests
                WHERE student_id = :id";
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                return $this->sendPayload(null, 'success', "Successfully deleted request.", 200);
            } else {
                $this->pdo->rollBack();
                return $this->sendPayload(null, 'failed', "No request found", 404);
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }

    public function rejectClassJoinRequest($id)
    {
        $sql = "DELETE FROM class_join_requests
                WHERE id = :id";
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                return $this->sendPayload(null, 'success', "Successfully deleted request.", 200);
            } else {
                $this->pdo->rollBack();
                return $this->sendPayload(null, 'failed', "No request found", 404);
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }

    public function cancelClassInvitation($id)
    {
        $sql = "DELETE FROM class_join_invitations
                WHERE student_id = :id";
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                return $this->sendPayload(null, 'success', "Successfully deleted invitation.", 200);
            } else {
                $this->pdo->rollBack();
                return $this->sendPayload(null, 'failed', "No invitation found", 404);
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }

    public function cancelClassInvitationByID($id)
    {
        $sql = "DELETE FROM class_join_invitations
                WHERE id = :id";
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                return $this->sendPayload(null, 'success', "Successfully deleted invitation.", 200);
            } else {
                $this->pdo->rollBack();
                return $this->sendPayload(null, 'failed', "No invitation found", 404);
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }


    public function clearClassJoinLinks()
    {
        $sql = "DELETE FROM class_join_links WHERE join_token_expires_at < NOW()";
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            $this->pdo->commit();
            return $this->sendPayload(null, 'success', "Successfully cleaned links.", 200);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }

    public function clearWarActivities($id)
    {
        $sql = "DELETE FROM student_war_activities
                WHERE war_id = :id";
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->pdo->commit();
            return $this->sendPayload(null, 'success', "Successfully deleted activities.", 200);

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }
    public function deleteWarActivity($id)
    {
        $sql = "DELETE FROM student_war_activities
                WHERE id = :id";
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->pdo->commit();
            return $this->sendPayload(null, 'success', "Successfully deleted activity.", 200);

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return $this->sendPayload(null, 'failed', $e->getMessage(), 500);
        }
    }
}