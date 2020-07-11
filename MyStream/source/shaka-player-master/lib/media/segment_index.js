/** @license
 * Copyright 2016 Google LLC
 * SPDX-License-Identifier: Apache-2.0
 */

goog.provide('shaka.media.SegmentIndex');
goog.provide('shaka.media.SegmentIterator');

goog.require('goog.asserts');
goog.require('shaka.media.SegmentReference');
goog.require('shaka.util.IReleasable');
goog.require('shaka.util.Timer');


/**
 * SegmentIndex.
 *
 * @implements {shaka.util.IReleasable}
 * @implements {Iterable.<!shaka.media.SegmentReference>}
 * @export
 */
shaka.media.SegmentIndex = class {
  /**
   * @param {!Array.<!shaka.media.SegmentReference>} references The list of
   *   SegmentReferences, which must be sorted first by their start times
   *   (ascending) and second by their end times (ascending).
   */
  constructor(references) {
    if (goog.DEBUG) {
      shaka.media.SegmentIndex.assertCorrectReferences_(references);
    }

    /** @private {!Array.<!shaka.media.SegmentReference>} */
    this.references_ = references;

    /** @private {shaka.util.Timer} */
    this.timer_ = null;

    /**
     * The number of references that have been removed from the front of the
     * array.  Used to create stable positions in the find/get APIs.
     *
     * @private {number}
     */
    this.numEvicted_ = 0;
  }


  /**
   * @override
   * @export
   */
  release() {
    this.references_ = [];

    if (this.timer_) {
      this.timer_.stop();
    }
    this.timer_ = null;
  }


  /**
   * Finds the position of the segment for the given time, in seconds, relative
   * to the start of the presentation.  Returns the position of the segment
   * with the largest end time if more than one segment is known for the given
   * time.
   *
   * @param {number} time
   * @return {?number} The position of the segment, or null if the position of
   *   the segment could not be determined.
   * @export
   */
  find(time) {
    // For live streams, searching from the end is faster.  For VOD, it balances
    // out either way.  In both cases, references_.length is small enough that
    // the difference isn't huge.
    for (let i = this.references_.length - 1; i >= 0; --i) {
      const r = this.references_[i];
      // Note that a segment ends immediately before the end time.
      if ((time >= r.startTime) && (time < r.endTime)) {
        return i + this.numEvicted_;
      }
    }
    if (this.references_.length && time < this.references_[0].startTime) {
      return this.numEvicted_;
    }

    return null;
  }


  /**
   * Gets the SegmentReference for the segment at the given position.
   *
   * @param {number} position The position of the segment as returned by find().
   * @return {shaka.media.SegmentReference} The SegmentReference, or null if
   *   no such SegmentReference exists.
   * @export
   */
  get(position) {
    if (this.references_.length == 0) {
      return null;
    }

    const index = position - this.numEvicted_;
    if (index < 0 || index >= this.references_.length) {
      return null;
    }

    return this.references_[index];
  }


  /**
   * Offset all segment references by a fixed amount.
   *
   * @param {number} offset The amount to add to each segment's start and end
   *   times.
   * @export
   */
  offset(offset) {
    for (const ref of this.references_) {
      ref.startTime += offset;
      ref.endTime += offset;
      ref.timestampOffset += offset;
    }
  }


  /**
   * Merges the given SegmentReferences.  Supports extending the original
   * references only.  Will not replace old references or interleave new ones.
   *
   * @param {!Array.<!shaka.media.SegmentReference>} references The list of
   *   SegmentReferences, which must be sorted first by their start times
   *   (ascending) and second by their end times (ascending).
   * @export
   */
  merge(references) {
    if (goog.DEBUG) {
      shaka.media.SegmentIndex.assertCorrectReferences_(references);
    }

    const lastOldReference = this.references_[this.references_.length - 1];

    let newReferences = [];
    if (!this.references_.length) {
      // There are no old references, so we take all of the new ones.
      newReferences = references;
    } else {
      // There are some old ones, so we only take the new ones that overlap with
      // the last old one or come after the last old one.
      newReferences = references.filter((r) => {
        return r.startTime >= lastOldReference.startTime;
      });
    }

    // It's valid to update the last reference in the old set.  If the first new
    // ref is a match for the last old one, we'll replace the old one.
    if (lastOldReference && newReferences.length &&
        newReferences[0].startTime == lastOldReference.startTime) {
      // Remove the last entry from the old set, and the first entry from the
      // new set will replace it in push(...) below.
      this.references_.pop();
    }

    this.references_.push(...newReferences);

    if (goog.DEBUG) {
      shaka.media.SegmentIndex.assertCorrectReferences_(this.references_);
    }
  }


  /**
   * Replace existing references with new ones, without merging.
   *
   * @param {!Array.<!shaka.media.SegmentReference>} newReferences
   * @export
   */
  replace(newReferences) {
    if (goog.DEBUG) {
      shaka.media.SegmentIndex.assertCorrectReferences_(newReferences);
    }
    this.references_ = newReferences;
  }


  /**
   * Removes all SegmentReferences that end before the given time.
   *
   * @param {number} time The time in seconds.
   * @export
   */
  evict(time) {
    const oldSize = this.references_.length;

    this.references_ = this.references_.filter((ref) => ref.endTime > time);

    const newSize = this.references_.length;
    const diff = oldSize - newSize;

    // Tracking the number of evicted refs will keep their "positions" stable
    // for the caller.
    this.numEvicted_ += diff;
  }


  /**
   * Also expands or contracts the last SegmentReference so it ends at the end
   * of its Period.
   *
   * Do not call on the last period of a live presentation (unknown duration).
   * It is okay to call on the other periods of a live presentation, where the
   * duration is known and another period has been added.
   *
   * @param {number} periodStart
   * @param {?number} periodEnd
   * @export
   */
  fit(periodStart, periodEnd) {
    goog.asserts.assert(periodEnd != null,
        'Period duration must be known for static content!');
    goog.asserts.assert(periodEnd != Infinity,
        'Period duration must be finite for static content!');

    // Trim out references we will never use.
    while (this.references_.length) {
      const lastReference = this.references_[this.references_.length - 1];
      if (lastReference.startTime >= periodEnd) {
        this.references_.pop();
      } else {
        break;
      }
    }

    while (this.references_.length) {
      const firstReference = this.references_[0];
      if (firstReference.endTime <= periodStart) {
        this.references_.shift();
        this.numEvicted_++;
      } else {
        break;
      }
    }

    if (this.references_.length == 0) {
      return;
    }

    // Adjust the last SegmentReference.
    const lastReference = this.references_[this.references_.length - 1];
    this.references_[this.references_.length - 1] =
        new shaka.media.SegmentReference(
            lastReference.startTime,
            /* endTime= */ periodEnd,
            lastReference.getUris,
            lastReference.startByte,
            lastReference.endByte,
            lastReference.initSegmentReference,
            lastReference.timestampOffset,
            lastReference.appendWindowStart,
            lastReference.appendWindowEnd);
  }


  /**
   * Updates the references every so often.  Stops when the references list
   * becomes empty.
   *
   * @param {number} interval The interval in seconds.
   * @param {function():!Array.<shaka.media.SegmentReference>} updateCallback
   * @export
   */
  updateEvery(interval, updateCallback) {
    goog.asserts.assert(!this.timer_, 'SegmentIndex timer already started!');
    this.timer_ = new shaka.util.Timer(() => {
      const references = updateCallback();
      this.references_.push(...references);
      if (this.references_.length == 0) {
        this.timer_.stop();
        this.timer_ = null;
      }
    });
    this.timer_.tickEvery(interval);
  }


  /** @return {!shaka.media.SegmentIterator} */
  [Symbol.iterator]() {
    return new shaka.media.SegmentIterator(this);
  }


  /**
   * Create a SegmentIndex for a single segment of the given start time and
   * duration at the given URIs.
   *
   * @param {number} startTime
   * @param {number} duration
   * @param {!Array.<string>} uris
   * @return {!shaka.media.SegmentIndex}
   * @export
   */
  static forSingleSegment(startTime, duration, uris) {
    const reference = new shaka.media.SegmentReference(
        /* startTime= */ startTime,
        /* endTime= */ startTime + duration,
        /* getUris= */ () => uris,
        /* startByte= */ 0,
        /* endByte= */ null,
        /* initSegmentReference= */ null,
        /* presentationTimeOffset= */ startTime,
        /* appendWindowStart= */ startTime,
        /* appendWindowEnd= */ startTime + duration);
    return new shaka.media.SegmentIndex([reference]);
  }
};


if (goog.DEBUG) {
  /**
   * Asserts that the given SegmentReferences are sorted.
   *
   * @param {!Array.<shaka.media.SegmentReference>} references
   * @private
   */
  shaka.media.SegmentIndex.assertCorrectReferences_ = (references) => {
    goog.asserts.assert(references.every((r2, i) => {
      if (i == 0) {
        return true;
      }
      const r1 = references[i - 1];
      if (r1.startTime < r2.startTime) {
        return true;
      } else if (r1.startTime > r2.startTime) {
        return false;
      } else {
        if (r1.endTime <= r2.endTime) {
          return true;
        } else {
          return false;
        }
      }
    }), 'SegmentReferences are incorrect');
  };
}


/**
 * An iterator over a SegmentIndex's references.
 *
 * @implements {Iterator.<shaka.media.SegmentReference>}
 * @export
 */
shaka.media.SegmentIterator = class {
  /** @param {shaka.media.SegmentIndex} segmentIndex */
  constructor(segmentIndex) {
    /** @private {shaka.media.SegmentIndex} */
    this.segmentIndex_ = segmentIndex;

    /** @private {number} */
    this.nextPosition_ = 0;
  }

  /**
   * Move the iterator to a given timestamp in the underlying SegmentIndex.
   *
   * @param {number} time
   * @return {shaka.media.SegmentReference}
   * @export
   */
  seek(time) {
    const position = this.segmentIndex_.find(time);
    if (position == null) {
      // An arbitrary, large number whose position will not find anything in the
      // segment index, even when incremented.
      this.nextPosition_ = 2**31;
      return null;
    }

    this.nextPosition_ = position;
    // Post-increment so that next() returns the one after this.
    return this.segmentIndex_.get(this.nextPosition_++);
  }

  /**
   * @return {shaka.media.SegmentReference}
   * @export
   */
  current() {
    return this.segmentIndex_.get(this.nextPosition_ - 1);
  }

  /**
   * @override
   * @export
   */
  next() {
    const ref = this.segmentIndex_.get(this.nextPosition_++);
    return {
      'value': ref,
      'done': !ref,
    };
  }
};
